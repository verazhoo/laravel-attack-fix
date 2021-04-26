<?php

require_once "HashTable.php";

function new_array() {
    return new HashTable();
}

/**
 * 看了一下似乎大致思路就是加 salt ？
 * 如果是这样的话我写的 HashTable 里已经加入了 salt
 * 抛开我写的 HashTable 里已经加入了 salt 不谈，似乎你这个加 salt 的实现方式也有点粗糙，性能是一个大问题~~~
 * 有其他一些有趣的观点可以发送到我的邮箱：2271439559@qq.com（不常上社区）
 */

function json_decode_replace($json) {
	$salt = salt();
	$pattern = '/"(\d+)":/';
	$json = preg_replace($pattern, '"${1}'. $salt  .'":', $json);
	$param = json_decode($json, true);
	$newArr = new_array();
	// $newArr = [];
	array_replace_key($param, $salt, $newArr);
	return  $newArr;
}


function array_replace_key($param, $salt, &$newArr) {

    /**
     * 你传入的 $newArr 是空数组，不应该使用 & 来改变原数组，return 就好了（在任何时候都应该警惕使用 &）
     * 因为 PHP 对于所有的赋值操作都是 Copy On Write 机制，你这样写反而会增加内存的使用
     * 建议你去看看关于这方面的文章，我贴两个鸟哥的：
     * https://www.laruence.com/2018/04/08/3170.html
     * https://www.laruence.com/2018/04/08/3179.html
     */

	foreach($param as $key => $value){
		// 匹配 \d
		$newKey = preg_replace('/^(\d+)'. $salt .'$/', '${1}', $key);
		if (is_array($value)) {
			loop_array($value, $salt, $newArr[$newKey]);
		} else {
			$newArr[$newKey] = $value;
		}
	}
}

function salt() {
	$salt = "abcdefghijklmnopqrstuvwxyz";
	$salt = $salt[mt_rand(0, 25)];
	return $salt;
}
