<?php

namespace models;
class BaseTest
{
    protected $bench_util;

    public static function moduleName()
    {
        return __CLASS__;
    }

    public function __construct(\BenchUtil $bench_util)
    {
        $this->bench_util = $bench_util;
        $this->init();
    }

    public function init()
    {

    }

    public function run()
    {
        $reflect = new \ReflectionClass($this);
        $class_doc = $reflect->getDocComment();
        $class_doc = self::getText($class_doc);

        $methods = $reflect->getMethods();

        foreach ($methods as $method) {
            if (substr($method->name, '0', 6) == 'action') {
                $method_doc = $method->getDocComment();
                $method_doc = self::getText($method_doc);
                $this->bench_util->addTask($method_doc, $this->$method);
            }
        }
        $this->bench_util->setModuleName($class_doc);
        $this->bench_util->run();
    }

    public static function getText($doc)
    {
        $rows = explode(PHP_EOL, $doc);
        foreach ($rows as $row) {
            if ($pos = mb_strpos($row, '@')) {
                return mb_substr($row, $pos + 1);
            }
        }
        return '';
    }
}