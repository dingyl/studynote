<?php

namespace base\structs;

abstract class AbstractRedis
{
    protected $redis;
    protected $key;
    private $lock;

    const EXPIRE_TIME = 1800;

    public function __construct($key)
    {
        $this->key = $key;
        $this->redis = \BaseModel::getHotWriteCache();
    }

    public function delete()
    {
        $this->redis->del($this->key);
    }

    public function updateExpire()
    {
        $this->redis->expire($this->key, self::EXPIRE_TIME);
    }

    public function exists()
    {
        return $this->redis->exists($this->key);
    }

    public function lock()
    {
        $resource = $this->key;
        $this->lock = $this->redis->lock($resource);
    }

    public function unlock()
    {
        if($this->lock){
            $this->redis->unlock($this->lock);
            $this->lock = null;
        }
    }

}