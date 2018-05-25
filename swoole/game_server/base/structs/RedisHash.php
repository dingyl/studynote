<?php

namespace base\structs;

class RedisHash extends AbstractRedis
{
    public function set($name, $value)
    {
        $this->redis->hset($this->key, $name, $value);
        $this->updateExpire();
    }

    public function get($name)
    {
        return $this->redis->hget($this->key, $name);
    }

    public function mSet($data)
    {
        $this->redis->hmset($this->key, $data);
        $this->updateExpire();
    }

    public function incr($name)
    {
        $this->redis->hincr($this->key, $name);
        $this->updateExpire();
    }

    public function decr($name)
    {
        # 框架中没有提供hdecr命令

        $this->lock();

        $value = $this->get($name);
        $value++;
        $this->set($name, $value);
        $this->updateExpire();

        $this->unlock();
    }

    public function detail()
    {
        return $this->redis->hgetall($this->key);
    }
}