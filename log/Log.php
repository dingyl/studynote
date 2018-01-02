<?php
include __DIR__.'/../utils.php';
class Log
{
    protected $_messages = [];
    protected $_log_file = '';
    protected static $_ins;
    public static function getIns(){
        if(!static::$_ins instanceof static){
            static::$_ins = new static();
        }
        return static::$_ins;
    }

    public function add(){
        $args = func_get_args();
        foreach ($args as $k=>$arg){
            if(!is_string($arg)){
                $args[$k] = json_encode($arg);
            }
        }
        $msg = implode(' ',$args);
        $this->_messages[] = $msg;
    }

    public function write(){
        $msg_str = '';
        foreach ($this->_messages as $message){

        }
    }

    public function debug(){
        $this->_messages[] = $this->debugInfo();
    }

    public function info(){
        $this->_messages[] = $this->debugInfo();
    }

    protected function debugInfo(){
        $time = date('m-d H:i:s');
        $backtrace = debug_backtrace();

        $backtrace_line = array_shift($backtrace); // 哪一行调用的log方法
        $backtrace_call = array_shift($backtrace); // 谁调用的log方法
        $file = substr($backtrace_line['file'], strlen($_SERVER['DOCUMENT_ROOT']));
        $line = $backtrace_line['line'];
        $class = isset($backtrace_call['class']) ? $backtrace_call['class'] : '';
        $type = isset($backtrace_call['type']) ? $backtrace_call['type'] : '';
        $func = $backtrace_call['function'];
        p($backtrace);
        $info = "$time $file:$line $class$type$func\n";
        echo $info;
        return $info;
    }
}