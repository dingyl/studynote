<?php

namespace base\structs;

# 有序集合

class RedisCollection extends AbstractRedis
{
    public function add($value)
    {
        $this->redis->zadd($this->key, time(), $value);
        $this->updateExpire();
    }

    public function remove($value)
    {
        $this->redis->zrem($this->key, $value);
        $this->updateExpire();
    }

    public function getAll()
    {
        return $this->redis->zrange($this->key, 0, -1);
    }
}