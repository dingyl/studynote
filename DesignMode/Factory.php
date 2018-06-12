<?php

# 工厂方法模式  定义一个用于创建对象的借口，让子类决定实例化哪一个类。工厂方法使一个类的实例化延迟到其子类

interface Animate{
    public function run();
    public function say();
}

class Cat implements Animate {
    public function __construct()
    {
        echo "this is cat";
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    public function say()
    {
        // TODO: Implement say() method.
    }
}


class Dog implements Animate{
    public function __construct()
    {
        echo "this is dog";
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    public function say()
    {
        // TODO: Implement say() method.
    }
}


abstract class Factory{
    abstract static function createAnimate();
}

class DogFactory extends Factory{
    public static function createAnimate(){
        return new Dog();
    }
}


class CatFactory extends Factory{
    public static function createAnimate()
    {
        return new Cat();
    }
}
