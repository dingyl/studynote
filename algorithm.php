<?php

//交换函数
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
            if ($arr[$j] > $arr[$j + 1]) {
                swap($arr[$j], $arr[$j + 1]);
            }
        }
    }
}

//快排
function quickSort(&$arr, $start, $end)
{
    if ($start < $end) {
        $left = $start;
        $right = $end;
        $middle = $arr[$start];
        while ($left < $right) {
            while ($left < $right && $arr[$right] > $middle) {
                $right--;
            }
            $arr[$left] = $arr[$right];
            while ($left < $right && $arr[$left] < $middle) {
                $left++;
            }
            $arr[$right] = $arr[$left];
        }
        $arr[$left] = $middle;
        quickSort($arr, $start, $left);
        quickSort($arr, $left + 1, $end);
    }
}

//php特殊实现快排
function quickSort2($arr)
{
    $length = count($arr);
    if ($length > 1) {
        $middle = $arr[0];
        $left = $right = [];
        foreach ($arr as $value) {
            if ($value < $middle) {
                $left[] = $value;
            } else {
                $right[] = $value;
            }
        }
        $left = quickSort2($left);
        $right = quickSort2($right);
        return array_merge($left, [$middle], $right);
    } else {
        return $arr;
    }
}


//合并排序
function merge(&$arr, $low, $middle, $high)
{
    $temp = [];
    $i = $k = $low;
    $j = $middle + 1;
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



##############################################################################
##############################################################################
##############################################################################



//全排列
function fullArrange(&$str, $index)
{
    if ($index == 0) {
        return [$str[0]];
    } else {
        $arranges = fullArrange($str, $index - 1);
        $temp = [];
        foreach ($arranges as $v) {
            for ($i = 0; $i <= strlen($v); $i++) {
                $temp[] = substr($v, 0, $i) . $str[$index] . substr($v, $i);
            }
        }
        return $temp;
    }
}

//全组合
function fullCombine(&$str, $index)
{
    if ($index == 0) {
        return [$str[0]];
    } else {
        $combines = fullCombine($str, $index - 1);
        $length = count($combines);
        $combines[] = $str[$index];
        for ($i = 0; $i < $length; $i++) {
            $combines[] = $combines[$i] . $str[$index];
        }
        return $combines;
    }
}

//print_r(fullArrange($str, strlen($str) - 1));
//print_r(fullCombine($str, strlen($str) - 1));


##############################################################################
##############################################################################
##############################################################################



//判断有序矩阵(从上到下，从左到右依次递增)中是否含有某个值
function contain(&$arr, $value)
{
    $row_num = count($arr);
    $line_num = count($arr[0]);
    $row = 0;
    $line = $line_num - 1;
    while ($row < $row_num && $line >= 0) {
        if ($arr[$row][$line] == $value) {
            return true;
        } else {
            if ($arr[$row][$line] < $value) {
                $row++;
            } else {
                $line--;
            }
        }
    }
    return false;
}




##############################################################################
##############################################################################
##############################################################################



//最长公共子序列长度
/**
 * 现有两个序列X={x1,x2,x3，...xi}，Y={y1,y2,y3，....，yi}，
 * 设一个C[i,j]: 保存Xi与Yj的LCS的长度。
 * c[i,j] = {
 * 0 , i=0或j=0
 * c[i-1,j-1]+1 若i,j>0,x[i]=y[j]
 * max(c[i,j-1],c[i-1,j]) 若i,j>0,x[i]!=y[j]
 * }
 */
function lcs($str1, $str2)
{
    $length1 = strlen($str1);
    $length2 = strlen($str2);
    $martix = [];
    for ($i = 0; $i <= $length1; $i++) {
        $martix[$i][0] = 0;
    }
    for ($j = 0; $j <= $length2; $j++) {
        $martix[0][$j] = 0;
    }
    for ($i = 1; $i <= $length1; $i++) {
        for ($j = 1; $j <= $length2; $j++) {
            if ($str1[$i - 1] == $str2[$j - 1]) {
                $martix[$i][$j] = $martix[$i - 1][$j - 1] + 1;
            } else {
                $martix[$i][$j] = max($martix[$i - 1][$j], $martix[$i][$j - 1]);
            }
        }
    }
    //最后一个元素的值就是最长公共子序列的长度 $martix[$length1][$length2]
}

//最小编辑距离
function editLength($str1, $str2)
{
    $length1 = strlen($str1);
    $length2 = strlen($str2);
    $martix = [];
    for ($i = 0; $i <= $length1; $i++) {
        $martix[$i][0] = 0;
    }
    for ($j = 0; $j <= $length2; $j++) {
        $martix[0][$j] = 0;
    }
    for ($i = 1; $i <= $length1; $i++) {
        for ($j = 1; $j <= $length2; $j++) {
            if ($str1[$i - 1] == $str2[$j - 1]) {
                $martix[$i][$j] = $martix[$i - 1][$j - 1];
            } else {
                $martix[$i][$j] = min($martix[$i - 1][$j], $martix[$i][$j - 1]) + 1;
            }
        }
    }
    //最后一个元素的值就是最小编辑距离 $martix[$length1][$length2]
}



/**
 * 背包问题
 * 给出n个物品的体积A[i]和其价值V[i]，将他们装入一个大小为m的背包，最多能装入的总价值有多大？
 * 例如：对于物品体积[2, 3, 5, 7]和对应的价值[1, 5, 2, 4], 假设背包大小为10的话，最大能够装入的价值为9。
 *
 * 思路：当空间为v时，对于任意一个物品i，如果i可以放入（v大于等于weight[i]），则此时v空间的价值f(v)等于f(v-weight[i]) + values[i]，因此通过遍历全部物品可以找到在空间为v时所能得到的最大值。
 * 代码：
 */
function backPack()
{

}




//最大连续和
function maxSum($arr)
{
    $length = count($arr);
    $curr_sum = 0;
    $max_sum = $arr[0];
    for ($i = 0; $i < $length; $i++) {
        $curr_sum = max($arr[$i], $arr[$i] + $curr_sum);
        $max_sum = max($max_sum, $curr_sum);
    }
    return $max_sum;
}

//快速选择算法 寻找最大或者最小的几个数  利用快排的思想来缩小数组范围
/**
 * 这个快速选择SELECT算法，类似快速排序的划分方法。
 * N个数存储在数组S中，再从数组中选取“中位数的中位数”作为枢纽元X，把数组划分为Sa和Sb俩部分，Sa<=X<=Sb，
 * 如果要查找的k个元素小于Sa的元素个数，则返回Sa中较小的k个元素，否则返回Sa中所有元素+Sb中小的k-|Sa|个元素，这种解法在平均情况下能做到O(n)的复杂度
 */
//待完善
function quickSelect(&$arr, $k, $start, $end)
{
    if ($end - $start + 1 >= $k) {
        $middle = intval(($start + $end) / 2);
        $left = $start;
        $right = $end;
        $middle_value = $arr[$middle];
        while ($left < $right) {
            while ($left < $right && $arr[$right] > $middle_value) {
                $right--;
            }
            $arr[$left] = $arr[$right];
            while ($left < $right && $arr[$left] < $middle_value) {
                $left++;
            }
            $arr[$right] = $arr[$left];
        }
        $arr[$left] = $middle_value;

    } else {
        echo "error";
        return [];
    }
}