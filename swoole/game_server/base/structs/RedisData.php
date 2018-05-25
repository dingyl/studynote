<?php

namespace base\structs;


class RedisData extends AbstractRedis
{
    public function set($value)
    {
        $this->redis->set($this->key, $value);
        $this->updateExpire();
    }

    public function get()
    {
        return $this->redis->get($this->key);
    }
}