<?
require_once "./HashTable/helper.php";
// 原生数组
$startTime = microtime(true);
$size = pow(2, 16);
$arr = array();
for ($key = 0, $maxKey = ($size - 1) * $size; $key <= $maxKey; $key += $size) {
    $arr[$key] = $key;
}
$endTime = microtime(true);
echo '构建hash数组耗时：'.($endTime - $startTime).'s'.PHP_EOL;


$startTimeA = microtime(true);
$json = json_encode($arr);
// $json = '{"11111111":[{"22222":"","user_name":"","user_privilege":3,"product_marks":[]}],"user_privilege":9,"update_date":"2021-04-15 18:01:49"}';
$param = json_decode_replace($json);
$endTimeA = microtime(true);
echo '重新构建hash数组耗时：'.($endTimeA - $startTimeA).'s'.PHP_EOL;

//var_dump(count($newArr));
