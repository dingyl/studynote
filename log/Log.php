<?php
include __DIR__.'/../utils.php';
include __DIR__.DIRECTORY_SEPARATOR.'LogException.php';
class Log
{
    public static function init(){
        set_exception_handler(['LogException','exceptionHandler']);
        set_error_handler(["LogException","errorHandler"]);
        register_shutdown_function(["shutdownHandler","shutdownHandler"]);
    }

    public static function write($data){

    }
}


Log::init();

throw(new LogException('打酱油',2));