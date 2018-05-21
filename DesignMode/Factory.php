<?php

# 工厂方法模式

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
