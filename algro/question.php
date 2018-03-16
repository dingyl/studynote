<?php
require_once 'base.php';
//解决问题的算法

//打印矩阵
function printRect($rect)
{
    $table = '<table >';
    foreach ($rect as $i => $arr) {
        $table .= '<tr>';
        foreach ($arr as $j => $value) {
            $table .= "<td>$value</td>";
        }
        $table .= '</tr>';
    }
    $table .= '</table>';
    echo $table;
    return $table;
}


//1. 最长公共子串(连续的)
function commonSubStr($stra, $strb)
{
    $lena = mb_strlen($stra);
    $lenb = mb_strlen($strb);
    $rect = [];
    for ($i = 0; $i <= $lena; $i++) {
        for ($j = 0; $j <= $lenb; $j++) {
            $rect[$i][$j] = 0;
        }
    }
    $max = 0;
    $index = 0;
    for ($i = 1; $i <= $lena; $i++) {
        $temp_str = mb_substr($stra, $i - 1, 1);
        for ($j = 1; $j <= $lenb; $j++) {
            $status = $temp_str == mb_substr($strb, $j - 1, 1) ? true : false;
            if ($status) {
                $rect[$i][$j] = $rect[$i - 1][$j - 1] + 1;
                if ($max < $rect[$i][$j]) {
                    $max = $rect[$i][$j];
                    $index = $i;
                }
            } else {
                $rect[$i][$j] = 0;
            }
        }
    }
    //存储最长公共子串
    $serias = mb_substr($stra, $index - $max, $max);
//    echo $serias . "<br/>";
    return $rect;
}

//2. 和最大的连续子数组(存在正负值，否者问题没有意义)
//2.1 分治法
function maxSumSerias($arr, $low, $high)
{
    if ($low == $high) {
        return max($arr[$low], 0);
    } else {
        //设置中间点
        $mid = intval(($low + $high) / 2);

        //左边最大值
        $left_sum = maxSumSerias($arr, $low, $mid);

        //右边最大值
        $right_sum = maxSumSerias($arr, $mid + 1, $high);

        //跨越中间最大值
        $total = 0;
        $left_max_sum = 0;
        for ($i = $mid; $i >= $low; $i--) {
            $total += $arr[$i];
            if ($left_max_sum < $total) {
                $left_max_sum = $total;
            }
        }
        $total = 0;
        $right_max_sum = 0;
        for ($i = $mid + 1; $i <= $high; $i++) {
            $total += $arr[$i];
            if ($right_max_sum < $total) {
                $right_max_sum = $total;
            }
        }
        $mid_sum = $left_max_sum + $right_max_sum;

        return max($left_sum, $right_sum, $mid_sum);
    }
}

//2.2 动态规划
function maxSum($arr)
{
    $len = count($arr);
    $value = 0;
    $sum = 0;
    $start = 0;//序列开始
    $t_start = 0;//存储临时值
    $end = 0;//序列结束
    for ($i = 0; $i < $len; $i++) {
        if($value>0){
            $value = $value + $arr[$i];
        }else{
            $value = $arr[$i];
            $t_start = $i;
        }
        if ($value > $sum) {
            $sum = $value;
            $start = $t_start;
            $end = $i;
        }
    }
    //其中一个和最大的子连续数组
//    echo "$start-$end<br/>";
    return $sum;
}


//3. 公共子序列(间续的)
function commonSubSequence($stra, $strb)
{
    $lena = mb_strlen($stra);
    $lenb = mb_strlen($strb);
    //存储公共子序列字符串
    $serias = '';
    $rect = [];
    for ($i = 0; $i <= $lena; $i++) {
        for ($j = 0; $j <= $lenb; $j++) {
            $rect[$i][$j] = 0;
        }
    }
    for ($i = 1; $i <= $lena; $i++) {
        $temp_str = mb_substr($stra, $i - 1, 1);
        for ($j = 1; $j <= $lenb; $j++) {
            $status = $temp_str == mb_substr($strb, $j - 1, 1) ? true : false;
            if ($status) {
                $rect[$i][$j] = $rect[$i - 1][$j - 1] + 1;
                if ($rect[$i-1][$j] == $rect[$i][$j - 1]) {
                    $serias .= $temp_str;
                }
            } else {
                $rect[$i][$j] = max($rect[$i - 1][$j], $rect[$i][$j - 1]);
            }
        }
    }
//    echo $serias . "<br/>";
    return $rect;
}

//4. 编辑距离  通过几次编辑能把一个字符串变成另一个字符串(有问题)
function editDistance($stra, $strb)
{
    $lena = mb_strlen($stra);
    $lenb = mb_strlen($strb);

    //去掉公共前缀
    $i = 0;
    while ($stra[$i] == $strb[$i]) {
        $i++;
        $lenb--;
        $lena--;
    }
    if ($i > 0) {
        $stra = mb_substr($stra, $i);
        $strb = mb_substr($strb, $i);
    }

    //去掉公共后缀
    $i = 0;
    while ($stra[$lena - $i] == $strb[$lenb - $i]) {
        $i++;
        $lenb--;
        $lena--;
    }
    if ($i > 0) {
        $stra = mb_substr($stra, 0, $lena);
        $strb = mb_substr($strb, 0, $lenb);
    }

    if ($lena == 0) {
        return $lenb;
    }

    if ($lenb == 0) {
        return $lena;
    }

    $rect = [];
    for ($i = 0; $i <= $lena; $i++) {
        for ($j = 0; $j <= $lenb; $j++) {
            $rect[$i][$j] = 0;
        }
    }

    //公共子序列
    for ($i = 1; $i <= $lena; $i++) {
        $temp_str = mb_substr($stra, $i - 1, 1);
        for ($j = 1; $j <= $lenb; $j++) {
            if ($temp_str == mb_substr($strb, $j - 1, 1)) {
                $rect[$i][$j] = $rect[$i - 1][$j - 1] + 1;
            } else {
                $rect[$i][$j] = max($rect[$i - 1][$j], $rect[$i][$j - 1]);
            }
        }
    }
    //最长字符串长度减去公共子序列的长度
//    echo "$lena $lenb ".$rect[$lena][$lenb]."<br/>";
    return max($lenb, $lena) - $rect[$lena][$lenb];
}

//5. 海明距离是对应位置进行比较，找出不同的字符个数
function hanDistance($stra, $strb)
{
    $lena = mb_strlen($stra);
    $lenb = mb_strlen($strb);
    $distance = 0;
    if ($lena == $lenb) {
        for ($i = 0; $i < $lena; $i++) {
            if ($stra[$i] != $strb[$i]) {
                $distance++;
            }
        }
        return $distance;
    } else {
        return false;
    }
}


$arr = [-10, 3, 6, 1, 4, -5, -23, 3, 3, -21];
//echo maxSumSerias($arr, 0, count($arr) - 1);
echo maxSum($arr);


//$str1 = "abcdfdsf";
//$str2 = "vxcvcdffvxcef";
//echo "<pre>";
//printRect(commonSubSequence($str1, $str2));
//printRect(commonSubStr($str1, $str2));