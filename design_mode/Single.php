<?php

require_once '../utils.php';

# 单例模式  确保某一个类只有一个实例

class Db
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
        if (empty(self::$ins)) {
            self::$ins = new static();
        }
        return self::$ins;
    }
}
