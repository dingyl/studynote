<?php
require_once "base.php";

//简单排序
function simpleSort(&$arr){
    $len = count($arr);
    for($i=0;$i<$len-1;$i++){
        for($j=$i+1;$j<$len;$j++){
            if($arr[$i]>$arr[$j]){
                swap($arr[$i],$arr[$j]);
            }
        }
    }
}


//冒泡排序
function bubbleSort(&$arr){
    $len = count($arr);
    for($i=1;$i<$len;$i++){
        for($j=0;$j<$len-$i;$j++){
            if($arr[$j]>$arr[$j+1]){
                swap($arr[$j],$arr[$j+1]);
            }
        }
    }
}

//快速排序
function quickSort(&$arr,$left,$right){
    if($left < $right){
        $key = $arr[$left];
        $low = $left;
        $high = $right;
        while($low < $high){
            while($low < $high && $arr[$high] > $key){
                $high--;
            }
            $arr[$low] = $arr[$high];
            while($low < $high && $arr[$low] < $key){
                $low++;
            }
            $arr[$high] = $arr[$low];
        }
        $arr[$low] = $key;
        quickSort($arr,$left,$low);
        quickSort($arr,$low+1,$right);
    }
}
