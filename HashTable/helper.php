<?php

require_once "HashTable.php";

function new_array() {
    return new HashTable();
}

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
