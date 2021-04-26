<?php

class Bucket implements  Countable
{

    /**
     * 原始key
     *
     * @var int|string
     */
    public $key = null;

    /**
     * 值
     *
     * @var mixed
     */
    public $value = NULL;

    /**
     * 处于同一个桶中的下一个元素
     *
     * @var Bucket|null
     */
    public $next = NULL;

    /**
     * 处于同一个桶中的上一个元素
     *
     * @var Bucket|null
     */
    public $last = NULL;

    /**
     * 下一个元素，用于线性遍历
     *
     * @var Bucket|null
     */
    public $listNext = NULL;

    /**
     * 上一个元素，用于线性遍历
     *
     * @var Bucket|null
     */
    public $listLast = NULL;

    public function __construct($key = null, $value = NULL, $last = NULL, $next = NULL)
    {
        $this->key = $key;
        $this->value = $value;
        $this->last = $last;
        $this->next = $next;
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
}