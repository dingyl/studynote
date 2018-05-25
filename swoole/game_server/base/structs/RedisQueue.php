<?php

namespace base\structs;

# 单向队列，循环队列

class RedisQueue extends AbstractRedis
{
    public function push($value)
    {
        $this->redis->lpush($this->key, $value);
        $this->updateExpire();
    }

    public function pop()
    {
        $value = $this->redis->rpop($this->key);
        $this->updateExpire();
        return $value;
    }

    # 循环取队列的下一个值
    public function next()
    {
        $value = $this->redis->rpop($this->key);
        $this->redis->lpush($this->key, $value);
        $this->updateExpire();
        return $value;
    }

    public function remove($value)
    {
        $this->redis->lrem($this->key, $value);
        $this->updateExpire();
    }

    public function getAll()
    {
        return $this->redis->lrange($this->key, 0, -1);
    }
}