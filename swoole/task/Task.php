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
    'action' => 'run',
    'data' => [
        'address' => '1907928206@qq.com',
        'title' => '美好的人生光景',
        'content' => '在浙江，督察组指出，省委、省政府重视环境保护，但对海洋生态环境管理方面强调较少，有些地方和部门抓工作不够有力。如国家明确重点海湾和重点河口区域禁止围填海，但2015年7月以来，浙江省海洋与渔业局在重点河口海湾违规审批44宗围填海项目。'
    ]
];
$client = new Task();
if ($resp = $client->addTask($task)) {
    echo $resp;
}
//$client->close();