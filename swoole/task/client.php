<?php
class Client
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        if (!$this->client->connect("127.0.0.1", 9501, 1) && !$this->client->isConnected()) {
            throw new Exception(sprintf('swoole error: %s', $this->client->errCode));
        }
    }

    public function send($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        $this->client->send($data);
        return $this->client->recv();
    }

    public function close()
    {
        $this->client->close();
    }
}

//双方统一传递json数据
$client = new Client();
if ($data = $client->send($data)) {
    echo $data;
}
$client->close();