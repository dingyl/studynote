<?php
require_once "base.php";
require_once "BalanceSortTree.php";
require_once "BinarySortTree.php";

echo "<pre>";
$root = new BalanceSortTree();
$arr = [5, 7, 6, 3, 8, 9, 1];
foreach ($arr as $value) {
    $root->insert($value);
}

$root->delete(3);

BalanceSortTree::levelDisplay($root);
