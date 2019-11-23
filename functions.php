<?php


function swap(&$a, &$b)
{
    $a ^= $b;
    $b ^= $a;
    $a ^= $b;
}

//冒泡排序
function bubbleSort(&$arr)
{
    $length = count($arr);
    for ($i = $length - 1; $i >= 0; $i--) {
        for ($j = 0; $j < $i; $j++) {
            if ($arr[$j] < $arr[$j + 1]) {
                swap($arr[$j], $arr[$j + 1]);
            }
        }
    }
}


// 有序数组合并
function merge(&$arr, $low, $middle, $high)
{
    $temp = [];
    $i = $low;
    $j = $middle + 1;
    $k = $low;
    while ($i <= $middle && $j <= $high) {
        if ($arr[$i] < $arr[$j]) {
            $temp[$k++] = $arr[$i++];
        } else {
            $temp[$k++] = $arr[$j++];
        }
    }
    while ($i <= $middle) {
        $temp[$k++] = $arr[$i++];
    }
    while ($j <= $high) {
        $temp[$k++] = $arr[$j++];
    }
    foreach ($temp as $index => $value) {
        $arr[$index] = $value;
    }
}

// 合并排序
function mergeSort(&$arr, $low, $high)
{
    if ($low < $high) {
        $middle = intval(($low + $high) / 2);
        mergeSort($arr, $low, $middle);
        mergeSort($arr, $middle + 1, $high);
        merge($arr, $low, $middle, $high);
    }
}

/*$arr = [2, 9, 5, 1, 7, 3, 8];
mergeSort($arr, 0, count($arr) - 1);
print_r($arr);*/


// 快排
function quickSort(&$arr, $start, $end)
{
    if ($start < $end) {
        $left = $start;
        $right = $end;
        $middle = $arr[$left];
        while ($left < $right) {
            while ($left < $right && $middle < $arr[$right]) {
                $right--;
            }
            $arr[$left] = $arr[$right];
            while ($left < $right && $middle > $arr[$left]) {
                $left++;
            }
            $arr[$right] = $arr[$left];
        }
        $arr[$left] = $middle;
        quickSort($arr, $start, $left);
        quickSort($arr, $left + 1, $end);
    }
}

/*
$arr = [2, 9, 5, 1, 7, 3, 8];
quickSort($arr);
print_r($arr);
*/


// 全排
function fullArrange(&$arr, $index)
{
    if ($index == 0) {
        return [$arr[0]];
    } else {
        $rows = fullArrange($arr, $index - 1);
        $temp_len = count($rows);
        $temp_arr = [];
        for ($k = 0; $k < $temp_len; $k++) {
            $row = $rows[$k];
            for ($j = 0; $j <= strlen($row); $j++) {
                $str = substr($row, 0, $j) . $arr[$index] . substr($row, $j);
                $temp_arr[] = $str;
            }
        }
        return $temp_arr;
    }
}

// 全组合
function fullCombine($arr, $index)
{
    if ($index == 0) {
        return [$arr[0]];
    } else {
        $rows = fullCombine($arr, $index - 1);
        $temp_len = count($rows);
        $rows[] = $arr[$index];
        for ($k = 0; $k < $temp_len; $k++) {
            $rows[] = $rows[$k] . $arr[$index];
        }
        return $rows;
    }
}

//$arr = "abc";
//$arranges = fullArrange($arr, 2);
//$combines = fullCombine($arr, 2);
//print_r($arranges);
//print_r($combines);