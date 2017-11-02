<?php
//熟悉self this的区别
//this是和对象绑定的
//self是和类绑定的，但是和文档
//static和类绑定的，但却代表
class Person{
    public static $age=23;

    private static $name="ding";
    public function getAge(){
        echo $this->age;
    }
}

class Son extends Person{
    public static $age=40;
    public function getAge(){
        echo parent::getAge();
    }
}

$son = new Son();
$son->getAge();



