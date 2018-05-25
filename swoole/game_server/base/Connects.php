<?php

# 连接管理
namespace base;

/**
 * id   连接句柄
 * user_id
 */
class Connects extends Model
{
    public $server;

    public function __construct($server, $fd)
    {
        parent::__construct($fd);
        $this->server = $server;
    }

    public function getDataHashKey($id)
    {
        return 'game_connect_' . $id;
    }
}