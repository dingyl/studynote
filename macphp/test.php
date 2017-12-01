<?php
include('functions.php');


/**
 * 图  最短路径问题
 */


//图的定义
$graph = [];
$graph['start'] = [];
$graph['start']['a']=6;
$graph['start']['b']=2;
$graph['a'] = [];
$graph['a']['fin'] = 1;
$graph['b'] = [];
$graph['b']['a'] = 3;
$graph['b']['fin'] = -1;
$graph['fin'] = [];

//从起点的开销
$cost = [];
$cost['start']=0;

//存储父节点
$parent = [];
$parent['start'] = NULL;
$parent['a'] = NULL;
$parent['b'] = NULL;
$parent['fin'] = NULL;

//存储处理过的节点
$process = [];

/**
 * 获取节点的所有邻居
 * @param $node
 * @return array
 */
function neighbor($node){
    return array_keys($node);
}


$queue = new SplQueue();
$queue->enqueue('start');
while(!$queue->isEmpty()){
    $node = $queue->dequeue();
    if(!in_array($node,$process)){
        $neighbors = neighbor($graph[$node]);
        foreach($neighbors as $neighbor){
            $costs = $graph[$node][$neighbor] + $cost[$node];
            if(!isset($cost[$neighbor]) || $cost[$neighbor]>$costs){
                $cost[$neighbor] = $costs;
                $parent[$neighbor] = $node;
            }
            $queue->enqueue($neighbor);
        }
        array_push($process,$node);
    }
}


//p($graph);
//p($cost);
//p($parent);
//p($process);



/**
 * 最长公共子串
 */

$str_a = "fosh";
$str_b = "fish";


function common_str($str_a,$str_b){
    $len_a = strlen($str_a);
    $len_b = strlen($str_b);
    $str_a = ' '.$str_a;
    $str_b = ' '.$str_b;
    $max_i = 0;
    $max_j = 0;
    $max_val = 0;
    $temp_arr = [];
    for($i=0;$i<=$len_a;$i++){
        for($j=0;$j<=$len_b;$j++){
            $temp_arr[$i][$j] = 0;
        }
    }
    for($i=1;$i<=$len_a;$i++){
        for($j=1;$j<=$len_b;$j++){
            if($str_a[$i]==$str_b[$j]){
                //最长公共子串
                $temp_arr[$i][$j] = $temp_arr[$i-1][$j-1] + 1;

                if($temp_arr[$i][$j]>$max_val){
                    $max_i = $i;
                    $max_j = $j;
                    $max_val = $temp_arr[$i][$j];
                }
            }
        }
    }
    return $temp_arr;
}




function common_xulie($str_a,$str_b){
    $len_a = strlen($str_a);
    $len_b = strlen($str_b);
    $str_a = ' '.$str_a;
    $str_b = ' '.$str_b;
    $max_i = 0;
    $max_j = 0;
    $max_val = 0;
    $temp_arr = [];
    for($i=0;$i<=$len_a;$i++){
        for($j=0;$j<=$len_b;$j++){
            $temp_arr[$i][$j] = 0;
        }
    }
    for($i=1;$i<=$len_a;$i++){
        for($j=1;$j<=$len_b;$j++){
            if($str_a[$i]==$str_b[$j]){
                //最长公共子序列
                $temp_arr[$i][$j] = $temp_arr[$i-1][$j-1] + 1;
            }else{
                $temp_arr[$i][$j] = max($temp_arr[$i-1][$j],$temp_arr[$i][$j-1]);
            }
        }
    }
    return $temp_arr;
}


function pvec($arrs){
    echo "<table>";
    foreach($arrs as $arr){
        echo "<tr>";
        foreach($arr as $v){
            echo "<td>$v</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

pvec(common_xulie($str_a,$str_b));




//背包问题