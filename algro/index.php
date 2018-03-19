<?php
require_once "base.php";
require_once "BalanceSortTree.php";
require_once "BinarySortTree.php";

echo "<pre>";
$root = new BalanceSortTree();
$arr = [3,8,5,7,6,2,1,10,4,9];
foreach($arr as $value){
    $root->insert($value);
}

BalanceSortTree::levelDisplay($root);
