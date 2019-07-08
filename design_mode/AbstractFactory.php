<?php

# 抽象工厂模式

interface Tv
{
    public function open();

    public function apply();
}

class HaierTv implements Tv
{
    public function open()
    {
        // TODO: Implement open() method.
    }

    public function apply()
    {
        // TODO: Implement apply() method.
    }
}


interface Pc
{
    public function work();

    public function play();
}

class LevenoPc implements Pc
{
    public function work()
    {
        // TODO: Implement work() method.
    }

    public function play()
    {
        // TODO: Implement play() method.
    }
}


abstract class Factory
{
    abstract static function createTv();

    abstract static function createPc();
}

class ProductFactory extends Factory
{
    public static function createTv()
    {
        return new HaierTv();
    }

    public static function createPc()
    {
        return new LevenoPc();
    }
}