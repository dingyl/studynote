<?php

namespace services;

use base\Connects;

class Route
{

    public $connect;

    public function __construct(Connects $connect)
    {
        $this->connect = $connect;
    }

    public function getAppliaction()
    {
        $data = $this->connect->detail();
        if (isset($data['game_code'])) {
            $code = strtolower($data['game_code']);
            $clazz = "\\games\\$code\\Application";
            if (class_exists($clazz)) {
                return new $clazz($this->connect);
            } else {
                info('类', $clazz, '不存在');
                return null;
            }
        } else {
            info('game_code参数不存在');
            return null;
        }
    }
}