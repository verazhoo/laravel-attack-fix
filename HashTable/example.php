<?php

require_once "helper.php";

//原生数组
$startTime = microtime(true);
$size = pow(2, 16);
$arr = array();
for ($key = 0, $maxKey = ($size - 1) * $size; $key <= $maxKey; $key += $size) {
    $arr[$key] = $key;
}
$endTime = microtime(true);
echo '原生数组用时：'.($endTime - $startTime).PHP_EOL;


//new_array
$startTime = microtime(true);
$newArr = new_array();
for ($key = 0, $maxKey = ($size - 1) * $size; $key <= $maxKey; $key += $size) {
    $newArr[$key] = $key;
}
$endTime = microtime(true);
echo 'new_array用时：'.($endTime - $startTime).PHP_EOL;

/*
foreach ($newArr as $key => $value) {
    echo "key：$key , value：$value".PHP_EOL;
}*/