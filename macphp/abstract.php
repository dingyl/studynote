<?php
abstract class Person{
    protected function __construct(){
        echo "this is person";
    }
}

class Son extends Person {
    public function __construct(){
        echo "this is son";
    }
}

$son = new Son();