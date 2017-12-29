<?php
class LogException extends Exception{
    public static function exceptionHandler($e){
        $file 	= $e->getFile();
        $line 	= $e->getLine();
        $code 	= $e->getCode();
        $message= $e->getMessage();
        if(Testlog::$log != null){
            if(class_exists('Log',false)){
                Log::$writeOnAdd = true;
                Testlog::$log->add(3,'['.$code.']:'.$message,array('file'=>$file,'line'=>$line));
            }
        }
    }
    /**
     * @desc 	错误处理函数
     *
     */
    public static function errorHandler($errno,$errstr,$errfile,$errline){
        self::exceptionHandler(new ErrorException($errstr,$errno,0,$errfile,$errline));
    }
    /**
     *
     *
     */
    public static function shutdownHandler(){
        $error = error_get_last();
        if($error){
            self::exceptionHandler(new ErrorException($error['message'],$error['type'],0,$error['file'],$error['line']));
        }
    }
}