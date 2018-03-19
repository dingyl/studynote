<?php

class Tree
{
    public $left;
    public $right;
    public $parent;
    public $value;

    //先序遍历
    public static function firstDisplay(Tree $tree)
    {
        echo $tree->value . "<br/>";
        if ($tree->left) {
            self::firstDisplay($tree->left);
        }
        if ($tree->right) {
            self::firstDisplay($tree->right);
        }
    }

    //中序遍历
    public static function middleDisplay(Tree $tree)
    {
        if ($tree->left) {
            self::middleDisplay($tree->left);
        }
        echo $tree->value . "<br/>";
        if ($tree->right) {
            self::middleDisplay($tree->right);
        }
    }

    //后续遍历
    public static function lastDisplay(Tree $tree)
    {
        if ($tree->left) {
            self::lastDisplay($tree->left);
        }
        if ($tree->right) {
            self::lastDisplay($tree->right);
        }
        echo $tree->value . "<br/>";
    }

    //深度优先遍历 类似 先序遍历
    public static function deepDisplay(Tree $tree)
    {
        $stack = new SplStack();
        $stack->push($tree);
        while (!$stack->isEmpty()) {
            $node = $stack->pop();
            echo $node->value . "<br/>";
            if ($node->left) {
                $stack->push($node->left);
            }
            if ($node->right) {
                $stack->push($node->right);
            }
        }
    }

    //广度优先遍历
    public static function levelDisplay(Tree $tree)
    {
        $queue = new SplQueue();
        $queue->enqueue($tree);
        while (!$queue->isEmpty()) {
            $node = $queue->dequeue();
            echo $node->value . "<br/>";
            if ($node->left) {
                $queue->enqueue($node->left);
            }
            if ($node->right) {
                $queue->enqueue($node->right);
            }
        }
    }
}

$root = new Tree();
$root->parent = null;
$root->value = 1;

$t2 = new Tree();
$t2->value = 2;
$t2->parent = $root;
$root->left = $t2;

$t3 = new Tree();
$t3->value = 3;
$t3->parent = $root;
$root->right = $t3;


$t4 = new Tree();
$t4->value = 4;
$t4->parent = $t2;
$t2->left = $t4;

$t5 = new Tree();
$t5->value = 5;
$t5->parent = $t2;
$t2->right = $t5;


$t6 = new Tree();
$t6->value = 6;
$t6->parent = $t3;
$t3->right = $t6;

$t7 = new Tree();
$t7->value = 7;
$t7->parent = $t6;
$t6->left = $t7;

$t8 = new Tree();
$t8->value = 8;
$t8->parent = $t6;
$t6->right = $t8;


Tree::levelDisplay($root);
//Tree::firstDisplay($root);