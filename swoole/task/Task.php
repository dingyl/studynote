<?php

/**
 * 任务客户端
 * Class Task
 */
class Task
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        if (!$this->client->connect("127.0.0.1", 9501, 1) && !$this->client->isConnected()) {
            throw new Exception(sprintf('swoole error: %s', $this->client->errCode));
        }
    }

    public function addTask($task)
    {
        $this->client->send(json_encode($task));
        return $this->client->recv();
    }

    public function close(){
        $this->client->close();
    }
}

$task = [
    'class' => 'MailTask',
    'data' => [
        'address' => '190@qq.com',
        'title' => 'title',
        'content' => 'content'
    ]
];
$client = new Task();
if ($resp = $client->addTask($task)) {
    echoLine($resp);
}
//$client->close();