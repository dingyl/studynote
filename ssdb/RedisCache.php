<?php

class RedisCache extends AbstractCache implements CacheInterface
{
    public static function getIns($host, $port)
    {
        if (!static::$ins instanceof static) {
            static::$ins = new static();
            static::$ins->db = new Redis();
            static::$ins->db->connect($host, $port);
        }
        return self::$ins;
    }

    public function type($key)
    {
        return $this->db->type($key);
    }

    public function delete($key)
    {
        $this->db->delete($key);
        return $this;
    }

    public function keys()
    {
        return $this->db->keys('*');
    }

    public function incr($key, $dept = 1)
    {
        $this->db->incrBy($key, $dept);
        return $this;
    }

    public function decr($key, $dept)
    {
        $this->db->decrBy($key, $dept);
        return $this;
    }

    public function sadd($key, $value)
    {
        $this->db->sAdd($key, $value);
        return $this;
    }

    public function scontains($key, $value)
    {
        return $this->db->sContains($key, $value);
    }

    public function scard($key){
        return $this->db->sCard($key);
    }

    public function sdel($key, $value)
    {
        $this->db->sRemove($key, $value);
        return $this;
    }

    public function sgetall($key)
    {
        return $this->db->sGetMembers($key);
    }

    public function sclear($key)
    {
        $argv = array_merge([$key],$this->sgetall($key));
        call_user_func_array([$this->db,'sRemove'],$argv);
        return $this;
    }

    public function zadd($key, $value, $sort)
    {
        $this->db->zAdd($key, $sort, $value);
        return $this;
    }

    public function zcontains($key, $value)
    {
        return in_array($value,$this->zgetall($key));
    }

    public function zcard($key)
    {
        return $this->db->zCard($key);
    }

    public function zrange($key, $start, $limit)
    {
        return $this->db->zRevRange($key, $start, $start+$limit-1);
    }

    public function zgetall($key)
    {
        return $this->db->zRevRange($key, 0, -1);
    }

    public function zclear($key)
    {
        return $this->db->zDeleteRangeByRank($key,'','');
    }

    public function zdel($key, $value)
    {
        $this->db->zDelete($key,$value);
        return $this;
    }

    public function hmset($key, $info)
    {
        $this->db->hMset($key, $info);
        return $this;
    }

    public function hset($key, $field, $value)
    {
        $this->db->hSet($key, $field, $value);
        return $this;
    }

    public function hgetall($key)
    {
        return $this->db->hGetAll($key);
    }

    public function hget($key, $field)
    {
        return $this->db->hGet($key, $field);
    }

    public function hclear($key)
    {
        $argv = array_merge([$key],$this->hgetall($key));
        call_user_func_array([$this->db,'hDel'],$argv);
        return $this;
    }

    public function hdel($key, $field)
    {
        $this->db->hDel($key, $field);
        return $this;
    }

    public function flushdb()
    {
        $this->db->flushDB();
        return $this;
    }

}