<?php
require_once "base.php";

$arr = [1, 3, 4, 5, 7, 8, 9, 23, 24, 53];

//二分查找
function binarySearch($arr, $value)
{
    $low = 0;
    $high = count($arr) - 1;
    while ($low < $high) {
        $mid = intval(($low + $high) / 2);
        if ($arr[$mid] == $value) {
            return $mid;
        }
        if ($arr[$mid] > $value) {
            $high = $mid - 1;
        } else {
            $low = $mid + 1;
        }
    }
    return -1;
}


echo binarySearch($arr, 2);