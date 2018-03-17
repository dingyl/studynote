<?php
require_once "base.php";

//用来解决路径问题

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