<?php
require_once 'Bucket.php';

class HashTable implements ArrayAccess, Countable
{
    /**
     * 加入 slat ，防止哈希碰撞
     *
     * @var string
     */
    protected  $salt = "abcdefghijklmnopqrstuvwxyz";

    /**
     * 数据集
     *
     * @var array
     */
    protected  $buckets = [];

    /**
     * 头元素，用于线性遍历
     *
     * @var Bucket|null
     */
    protected $listHead = null;

    /**
     * 尾元素，用于线性遍历
     *
     * @var Bucket|null
     */
    protected $listTail = null;

    /**
     * 当前元素，用于线性遍历
     *
     * @var Bucket|null
     */
    protected $listCurrent = null;

    public function __construct()
    {
        //随机选择一个字符
        $this->salt = $this->salt[mt_rand(0,9)];
    }

    /**
     * djb2 哈希算法
     *
     * @see https://theartincode.stanis.me/008-djb2/
     *
     * @param string $key
     * @return int
     */
    public function hashCode (string $key)
    {
        $hash = 5381;
        $keyLen = strlen($key);

        for ($i = 0; $i < $keyLen; $i++) {
            $hash = (($hash << 5) + $hash) + ord($key[$i]); // 等同于：($hash * 33 + $hash) + ord($key[$i])
        }

        return $hash & 0x7FFFFFFF; // & 0x7FFFFFFF 保证计算值始终为正数并且不溢出
    }
    /**
     * @see Countable::count
     * @return int
     */
    public function count()
    {
        static $count = 0;
        return ++$count;
    }

    /**
     * 写入数据
     *
     * @param int|string $key
     * @param mixed $value
     * @return Bucket|null
     */
    public function put($key, $value)
    {
        $hash = $this->getHash($key);
        $bucket = null;
        if (isset($this->buckets[$hash])) {

            $lastBucket = $this->buckets[$hash];
            //如果 key 已经存在，则覆盖写入并返回
            if ($key === $lastBucket->key) {
                $lastBucket->value = $value;
                return $lastBucket;
            }

            //循环至最后一个节点
            while ($lastBucket->next) {
                $lastBucket = $lastBucket->next;

                //如果 key 已经存在，则覆盖写入并返回
                if ($key === $lastBucket->key) {
                    $lastBucket->value = $value;
                    return $lastBucket;
                }
            }
            $lastBucket->next = $bucket = new Bucket($key, $value, $lastBucket);
        } else {
            $this->buckets[$hash] = $bucket = new Bucket($key, $value);
        }
        $this->change($bucket);

        return $bucket;
    }

    /**
     * 当有改动时，改变线性遍历属性
     *
     * @param Bucket $bucket
     * @return void
     */
    protected function change (Bucket $bucket)
    {
        if ($this->listHead === null) {
            $this->listHead = $bucket;
            $this->listCurrent = $bucket;
        } else {
            $this->listTail->listNext = $bucket;
            $bucket->listLast = $this->listTail;
        }
        $this->listTail = $bucket;
    }

    /**
     * 获取数据
     *
     * @param int|string $key
     * @return mixed|null
     */
    public function get($key)
    {
        $hash = $this->getHash($key);
        if (!isset($this->buckets[$hash])) {
            return null;
        }

        $bucket = $this->buckets[$hash];
        if ($key === $bucket->key) {
            return $bucket->value;
        }

        while ($bucket->next) {
            if ($key === $bucket->key) {
                return $bucket->value;
            }
            $bucket = $bucket->next;
        }

        return null;
    }

    /**
     * 索引数据是否存在
     *
     * @param int|string $key
     * @return bool
     */
    public function exists($key)
    {
        $hash = $this->getHash($key);
        if (!isset($this->buckets[$hash])) {
            return false;
        }

        $bucket = $this->buckets[$hash];
        if ($key === $bucket->key) {
            return true;
        }

        while ($bucket->next) {
            if ($key === $bucket->key) {
                return true;
            }
            $bucket = $bucket->next;
        }

        return false;
    }

    /**
     * 移除数据
     *
     * @param int|string $key
     * @return bool|null
     */
    public function remove($key)
    {
        $hash = $this->getHash($key);
        if (!isset($this->buckets[$hash])) {
            return null;
        }

        $bucket = $this->buckets[$hash];
        //如果 next 为 NULL，证明此 bucket 中只有一个元素，所以直接删除数据集的索引
        if ($key === $bucket->key && $bucket->next === null) {
            $this->reposition($this->buckets[$hash]);
            unset($bucket, $this->buckets[$hash]);
            return true;
        } else if ($key === $bucket->key){
            $this->buckets[$hash] = $bucket->next;
            $this->reposition($bucket);
            unset($bucket);
            return true;
        }

        while ($bucket->next) {
            $bucket = $bucket->next;

            //如果 next 为 NULL，证明此 bucket 为最后一个元素
            if ($key === $bucket->key && $bucket->next === null) {
                $bucket->last->next = null;
                $this->reposition($bucket);
                unset($bucket);
                return true;
            } else if ($key === $bucket->key){
                $bucket->last->next = $bucket->next;
                $this->reposition($bucket);
                unset($bucket);
                return true;
            }
        }

        return null;
    }

    /**
     * 调整线性遍历属性
     *
     * @param Bucket $bucket
     * @return void
     */
    protected function reposition(Bucket $bucket)
    {
        if ($bucket === $this->listHead && $this->listHead->listNext !== null) {
            $this->listHead->listNext->listLast = null;
            $this->listHead = $this->listHead->listNext;
        } else if ($bucket === $this->listHead) {
            $this->listHead = null;
        }

        if ($bucket === $this->listCurrent && $this->listCurrent->listNext !== null) {
            $this->listCurrent->listNext->listLast = null;
            $this->listCurrent = $this->listCurrent->listNext;
        } else if ($bucket === $this->listCurrent && $this->listCurrent->listLast !== null) {
            $this->listCurrent = $this->listCurrent->listLast;
        } else if ($bucket === $this->listCurrent) {
            $this->listCurrent = null;
        }

        if ($bucket === $this->listTail && $this->listTail->listLast !== null) {
            $this->listTail->listLast->listNext = null;
            $this->listTail = $this->listTail->listLast;
        } else if ($bucket === $this->listTail) {
            $this->listTail = null;
        }
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->listCurrent->value;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->listCurrent = $this->listCurrent->listNext;
        return $this->listCurrent->value;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->listCurrent->key;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->listCurrent->key ? true : false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->listCurrent = $this->listHead;
    }

    /**
     * 获取哈希值
     *
     * @param string|int $key
     * @return int
     */
    protected function getHash($key)
    {
        return $this->hashCode((string)$key.$this->salt);
    }

    public function offsetSet($offset, $value)
    {
        return $this->put($offset, $value);
    }

    public function offsetExists($offset)
    {
       return $this->exists($offset);
    }

    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function buckets()
    {
        return $this->buckets;
    }

}
