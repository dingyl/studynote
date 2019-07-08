<?php
require_once '../utils.php';

# 建造者模式

abstract class CarModel
{
    protected $sequence = [];

    abstract public function start();

    abstract public function stop();

    abstract public function alarm();

    abstract public function engineBoom();

    final public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    final public function run()
    {
        foreach ($this->sequence as $func) {
            $this->$func();
        }
    }
}


class BmwModel extends CarModel
{
    public function stop()
    {
        echoLine('bmw stop');
    }

    public function start()
    {
        echoLine('bmw start');
    }

    public function engineBoom()
    {
        echoLine('bmw engine_boom');
    }

    public function alarm()
    {
        echoLine('bmw alarm');
    }
}


class BenzModel extends CarModel
{
    public function start()
    {
        echoLine('benz start');
    }

    public function stop()
    {
        echoLine('benz stop');
    }

    public function engineBoom()
    {
        echoLine('benz engine_boom');
    }

    public function alarm()
    {
        echoLine('benz alarm');
    }
}


abstract class Builder
{
    abstract public function createCar();

    abstract public function setSequence($sequence);
}


class BmwBuilder extends Builder
{
    protected $car;

    public function __construct()
    {
        $this->car = new BmwModel();
    }

    public function createCar()
    {
        return $this->car;
    }

    public function setSequence($sequence)
    {
        $this->car->setSequence($sequence);
    }
}


class BenzBuilder extends Builder
{
    protected $car;

    public function __construct()
    {
        $this->car = new BenzModel();
    }

    public function createCar()
    {
        return $this->car;
    }

    public function setSequence($sequence)
    {
        $this->car->setSequence($sequence);
    }
}

class Director
{
    public static function getBmwModel()
    {
        $sequence = ['start', 'alarm'];
        $bmw_builder = new BmwBuilder();
        $bmw_builder->setSequence($sequence);
        return $bmw_builder->createCar();
    }

    public static function getBenzModel()
    {
        $sequence = ['start', 'alarm', 'engineBoom', 'stop'];
        $benz_builder = new BenzBuilder();
        $benz_builder->setSequence($sequence);
        return $benz_builder->createCar();
    }
}

$bmw = Director::getBmwModel();
$bmw->run();

$benz = Director::getBenzModel();
$benz->run();