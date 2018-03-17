<?php
require_once "base.php";
require_once "BalanceSortTree.php";

$tree = new BalanceSortTree();
$tree->insert(1);
$tree->insert(2);
$tree->insert(3);
BalanceSortTree::levelDisplay($tree);
echo $tree->getHeight();
