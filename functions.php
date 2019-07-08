<?php

// 有序数组合并
function merge(&$arr, $low, $middle, $high)
{
    $left_arr = $right_arr = [];
    for ($i = $low; $i <= $middle; $i++) {
        $left_arr[] = $arr[$i];
    }
    for ($j = $middle + 1; $j <= $high; $j++) {
        $right_arr[] = $arr[$j];
    }
    $i = $j = 0;
    $k = $low;
    while (true && $i < count($left_arr) && $j < count($right_arr)) {
        if ($left_arr[$i] < $right_arr[$j]) {
            $arr[$k++] = $left_arr[$i++];
        } else {
            $arr[$k++] = $right_arr[$j++];
        }
    }
    while ($i < count($left_arr)) {
        $arr[$k++] = $left_arr[$i++];
    }
    while ($j < count($right_arr)) {
        $arr[$k++] = $right_arr[$j++];
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

// 快排
function quickSort(&$arr, $low, $high)
{
    if ($low < $high) {
        $left = $k = $low;
        $right = $high;
        $middle = $arr[$k];
        while ($left < $right) {
            while ($left < $right && $arr[$right] >= $middle) {
                $right--;
            }
            if ($left < $right) {
                $arr[$k] = $arr[$right];
                $k = $right;
            }
            while ($left < $right && $arr[$left] <= $middle) {
                $left++;
            }
            if ($left < $right) {
                $arr[$k] = $arr[$left];
                $k = $left;
            }
        }
        $arr[$left] = $middle;
        quickSort($arr, $low, $k);
        quickSort($arr, $k + 1, $high);
    }
}

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
