<?php

class Demo
{
    public $name;
}

$demo1 = new Demo();
$demo2 = new Demo();

var_dump($demo1 === $demo2);

$arr = [];
array_push($arr, $demo1);
array_push($arr, $demo2);
var_dump(array_search($demo2, $arr));

# array_search 搜索采用==匹配模式，不是===

class Son
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

$arr = [];

$son1 = new Son('ding1');
$son2 = new Son('ding2');
$son3 = new Son('ding3');
$son4 = new Son('ding4');

array_push($arr, $son1);
array_push($arr, $son2);
array_push($arr, $son3);
array_push($arr, $son4);

var_dump(array_search($son2, $arr));


