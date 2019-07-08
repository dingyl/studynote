<?php
function xrange($start, $end, $step = 1) {
    for ($i = $key = $start; $i <= $end; $i += $step) {
        yield $key => $i ;
    }
}


function xrange1($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i ;
    }
}

//yield可以在没有参数传入的情况下被调用来生成一个 NULL值并配对一个自动的键名
function gen_three_nulls() {
    foreach (range(1, 3) as $i) {
        yield;
    }
}


function count_to_ten() {
    yield 1;
    yield 2;
    yield from [3, 4];
    yield from new ArrayIterator([5, 6]);
    yield from seven_eight();
    yield 9;
    yield 10;
}

function seven_eight() {
    yield 7;
    yield from eight();
}

function eight() {
    yield 8;
}

foreach (count_to_ten() as $num) {
    echo "$num ";
}

//以上例程会输出：1 2 3 4 5 6 7 8 9 10

//这个语法可以和生成器对象的Generator::send()方法配合使用。
//yield指令提供了任务中断自身的一种方法

$range = xrange(1, 100);
var_dump($range); // object(Generator)#1
var_dump($range instanceof Iterator); // bool(true)


function gen() {
    $ret = (yield 'yield1');
    var_dump($ret);
    $ret = (yield 'yield2');
    var_dump($ret);
}

//$gen = gen(); //返回一个生成器
//var_dump($gen->current());    // 执行gen函数到(yield 'yield1')后中断,返回 'yield'字符,并中断gen函数执行
//var_dump($gen->send('ret1'));
// 发送'ret1',唤醒gen函数继续执行,gen中的 (yield 'yield1') 返回'ret1'赋值给$ret,后继续执行到(yield 'yield2')中断返回'yield2'
