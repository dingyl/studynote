<?php

# 单例模式

class Model
{
    private static $ins;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getIns()
    {
        if (!self::$ins instanceof self) {
            self::$ins = new self();
        }
        return self::$ins;
    }
}


$model1 = Model::getIns();
$model2 = Model::getIns();
var_dump($model1 === $model2);