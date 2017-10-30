<?php
$stack = new SplStack();
$stack->push("1");
$stack->push("2");
$stack->push("3");


while(!$stack->isEmpty()){
    echo $stack->pop()."<br/>";
}

$quene = new SplQueue();
$quene->enqueue("a");
$quene->enqueue("b");
$quene->enqueue("c");
while(!$quene->isEmpty()){
    echo $quene->dequeue()."<br>";
}
