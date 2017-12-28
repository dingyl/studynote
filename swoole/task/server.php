#!/usr/bin/env php
<?php
class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1, //一般设置为服务器CPU数的1-4倍
            'daemonize' => 1, //以守护进程执行
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'task_worker_num' => 8, //task进程的数量
            "task_ipc_mode " => 3, //使用消息队列通信，并设置为争抢模式
            "log_file" => "demo.log" ,//所有的输出都会写到日志中
        ));
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('task', [$this, 'onTask']);
        $this->serv->on('finish', [$this, 'onFinish']);
        $this->serv->start();
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        //接收数据，下发任务
        $serv->task($data);
        $serv->send($fd,'任务ok啦');
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        //任务处理
        $task = json_decode($data);
    }

    public function onFinish($serv, $task_id, $data)
    {
        //任务结束，回调
        echo "finish";
    }
}

$server = new Server();