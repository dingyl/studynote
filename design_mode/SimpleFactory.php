<?php
# 简单工厂模式

class Dog{

    public function __construct()
    {
        echo "this is dog";
    }

    public function show()
    {

    }
}


class Cat{
    public function __construct()
    {
        echo "this is cat";
    }


    public function show()
    {

    }
}


class SimpleFactory{
    public static function createAnimate($name)
    {
        switch($name){
            case 'dog':
                return new Dog();
                break;
            case 'cat':
                return new Cat();
                break;
            default:
                return null;
                break;
        }
    }
}

