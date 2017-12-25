<?php

class SsdbCache extends AbstractCache implements CacheInterface
{

    public static function getIns($host, $port)
    {
        if (!static::$ins instanceof static) {
            static::$ins = new static();
            static::$ins->db = new SSDB($host, $port);
        }
        return static::$ins;
    }

    public function delete($key)
    {
        $this->db->del($key);
        return $this;
    }

    public function keys()
    {
        return $this->db->keys('', '', PHP_INT_MAX);
    }

    public function incr($key, $dept = 1)
    {
        $this->db->incr($key, $dept);
        return $this;
    }

    public function decr($key, $dept = 1)
    {
        $this->db->decr($key, $dept);
        return $this;
    }

    public function sadd($key, $value)
    {
        $this->zadd($key, $value, 1);
        return $this;
    }

    public function scontains($key, $value)
    {
        return $this->zcontains($key, $value);
    }

    public function scard($key)
    {
        return $this->zcard($key);
    }

    public function sdel($key, $value)
    {
        $this->zdel($key, $value);
        return $this;
    }

    public function sgetall($key)
    {
        return $this->zgetall($key);
    }

    public function sclear($key)
    {
        $this->zclear($key);
    }

    public function zadd($key, $value, $sort)
    {
        $this->db->zset($key, $value, $sort);
        return $this;
    }

    public function zcontains($key, $value)
    {
        return $this->db->zexists($key, $value);
    }

    public function zcard($key)
    {
        return $this->db->zcount($key, '', '');
    }

    public function zrange($key, $start, $limit)
    {
        return $this->db->zrange($key, $start, $limit);
    }

    public function zgetall($key)
    {
        return $this->db->zscan($key, '', '', '', PHP_INT_MAX);
    }

    public function zclear($key)
    {
        $this->db->zclear($key);
        return $this;
    }

    public function zdel($key, $value)
    {
        $this->db->zdel($key, $value);
        return $this;
    }

    public function hmset($key, $info)
    {
        $this->db->multi_hset($key, $info);
        return $this;
    }

    public function hset($key, $field, $value)
    {
        $this->db->hset($key, $field, $value);
    }

    public function hgetall($key)
    {
        return $this->db->hgetall($key);
    }

    public function hget($key, $field)
    {
        return $this->db->hget($key, $field);
    }

    public function hdel($key, $field)
    {
        $this->db->hdel($key, $field);
        return $this;
    }

    public function hclear($key)
    {
        $this->db->hclear($key);
    }

    public function flushdb()
    {
        $this->db->flushdb();
        return $this;
    }
}