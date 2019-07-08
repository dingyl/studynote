<?php
$queue = new SplQueue();


class Tree{
    public $left;
    public $right;
    public $data;
    public $status;
    public $pwd = [];
    public function __construct($data){
        $this->data = $data ;
        $this->left = null ;
        $this->right = null ;
        $this->status = 0;
    }

    public function init(Tree $tree){

    }
}

$root = new Tree(0);
$t1 = new Tree(1);
$t2 = new Tree(2);
$t3 = new Tree(3);
$t4 = new Tree(4);
$t5 = new Tree(5);
$t6 = new Tree(6);
$t7 = new Tree(7);
$t8 = new Tree(8);


$root->left = $t1 ;
$root->right = $t2 ;
$t1->left = $t3 ;
$t1->right = $t4 ;
$t2->left = $t5 ;
$t2->right = $t6 ;
$t4->left = $t7 ;

$root->status = 1;
$queue->enqueue($root);
while(!$queue->isEmpty()){
    $t = $queue->dequeue();
    echo $t->data."<br/>";
    (!empty($t->left) && $t->left->status==0) && ($t->left->status=1 && $queue->enqueue($t->left));
    (!empty($t->right) && $t->right->status==0) && ($t->right->status=1 && $queue->enqueue($t->right));
    $t->status = 2 ;
}


echo uniqid();




