<?php

namespace base;

abstract Class AbstractApplication
{
    protected $connect;

    public $error_reason = '';

    public function __construct(Connects $connect)
    {
        $this->connect = $connect;
    }

    abstract public function unNeedCheckAction($action_name);

    abstract public static function name();

    abstract public function beforeAction();

    abstract public function afterOpen();

    abstract public function beforeClose();
}