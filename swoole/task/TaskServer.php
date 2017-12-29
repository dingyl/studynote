#!/usr/bin/env php
<?php
//定义任务常量
include __DIR__.'/../../utils.php';
define('TASK_DIR',__DIR__);
define('TASK_EXT','php');
class TaskServer
{
    private $serv;

    public function __construct()
    {
        $this->registerAutoload();
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1,          //一般设置为服务器CPU数的1-4倍
            'daemonize' => 1,           //以守护进程执行
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'task_worker_num' => 8,     //task进程的数量
            "task_ipc_mode " => 3,      //使用消息队列通信，并设置为争抢模式
            "log_file" => "task.log"    //所有的输出都会写到日志中
        ));
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('task', [$this, 'onTask']);
        $this->serv->on('finish', [$this, 'onFinish']);
        $this->serv->start();
    }

    /**
     * 添加任务自动加载器
     */
    public function registerAutoload(){
        spl_autoload_register(function ($class) {
            include TASK_DIR .DIRECTORY_SEPARATOR.ucwords($class).'.'.TASK_EXT;
        });
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        //接收数据，下发任务
        $serv->task($data);
        //告诉客服端任务已经接受下发
        $serv->send($fd,'ok');
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        //任务处理
        $task = json_decode($data,true);
        echoLine($task);
        if(isset($task['class'])){
            $class = $task['class'];
            $class = new $class();
            echoLine($task_id."任务开始执行");
            $class->run($task['data']);
        }else{
            echoLine($task_id."任务异常");
        }
    }

    public function onFinish($serv, $task_id, $data)
    {
        //任务结束，回调
        echoLine($task_id."任务结束");
    }
}
$server = new TaskServer();