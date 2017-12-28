<?php
interface CacheInterface{

    public function set($key,$value);
    public function expire($key,$time);
    public function ttl($key);
    public function get($key);
    public function exists($key);
    public function delete($key);
    public function keys();

    public function incr($key,$dept);
    public function decr($key,$dept);

    public function sadd($key,$value);
    public function scontains($key,$value);
    public function scard($key);
    public function sdel($key,$value);
    public function sgetall($key);
    public function sclear($key);

    public function zadd($key,$value,$sort);
    public function zcontains($key,$value);
    public function zcard($key);
    public function zrange($key,$start,$limit);
    public function zgetall($key);
    public function zclear($key);
    public function zdel($key,$value);

    public function hmset($key,$info);
    public function hset($key,$field,$value);
    public function hgetall($key);
    public function hget($key,$field);
    public function hdel($key,$field);
    public function hclear($key);

    public function qpush($key,$value);
    public function qpop($key);

    public function flushdb();
}