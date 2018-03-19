<?php
function swap(&$a, &$b)
{
    $a ^= $b;
    $b ^= $a;
    $a ^= $b;
}

//简单排序
function simpleSort(&$arr)
{
    $len = count($arr);
    for ($i = 0; $i < $len - 1; $i++) {
        for ($j = $i + 1; $j < $len; $j++) {
            if ($arr[$i] > $arr[$j]) {
                swap($arr[$i], $arr[$j]);
            }
        }
    }
}


//冒泡排序
function bubbleSort(&$arr)
{
    $len = count($arr);
    for ($i = 1; $i < $len; $i++) {
        for ($j = 0; $j < $len - $i; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                swap($arr[$j], $arr[$j + 1]);
            }
        }
    }
}

//快速排序
function quickSort(&$arr, $left, $right)
{
    if ($left < $right) {
        $key = $arr[$left];
        $low = $left;
        $high = $right;
        while ($low < $high) {
            while ($low < $high && $arr[$high] > $key) {
                $high--;
            }
            $arr[$low] = $arr[$high];
            while ($low < $high && $arr[$low] < $key) {
                $low++;
            }
            $arr[$high] = $arr[$low];
        }
        $arr[$low] = $key;
        quickSort($arr, $left, $low);
        quickSort($arr, $low + 1, $right);
    }
}

//归并排序
function mergeSort(&$arr, $low, $high)
{
    if ($low < $high) {
        $mid = intval(($low + $high) / 2);
        mergeSort($arr, $low, $mid);
        mergeSort($arr, $mid + 1, $high);
        merge($arr, $low, $mid, $high);
    }
}


//合并两个有序子数组
function merge(&$arr, $low, $mid, $high)
{
    $temp = [];
    $m = $low;
    $n = $mid + 1;
    while ($m <= $mid && $n <= $high) {
        if ($arr[$m] < $arr[$n]) {
            array_push($temp, $arr[$m++]);
        } else {
            array_push($temp, $arr[$n++]);
        }
    }

    while ($m <= $mid) {
        array_push($temp, $arr[$m++]);
    }

    while ($n <= $high) {
        array_push($temp, $arr[$n++]);
    }

    for ($i = 0; $i < count($temp); $i++) {
        $arr[$low + $i] = $temp[$i];
    }
}
