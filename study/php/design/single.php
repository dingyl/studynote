<?php
class Single{
    static private $_instance = null;
    final protected function __construct(){

    }

    final protected function __clone(){

    }

    static public function getIns(){
        static::$_instance==null && (static::$_instance = new static());
        return static::$_instance;
    }

    public function test(){
        echo "this is test";
    }
}