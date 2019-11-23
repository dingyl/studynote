<?php


abstract class AbstractBloomFilter
{
    protected $Redis;

    protected $hashFunction; // 指定采用的hash函数数组

    protected $Hash; // filter hash 函数类

    protected $bucket;  // 指定存储位的key名称

    public function __construct()
    {
        $this->Hash = new FilterHash();
        $this->Redis = new \Redis();
        $this->Redis->connect('127.0.0.1');
    }

    public function add($string)
    {
        $pipe = $this->Redis->multi();
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->$function($string);
            $pipe->setBit($this->bucket, $hash, 1);
        }
        return $pipe->exec();
    }

    public function exists($string)
    {
        $pipe = $this->Redis->multi();
        $len = strlen($string);
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->$function($string, $len);
            $pipe = $pipe->getBit($this->bucket, $hash);
        }
        $res = $pipe->exec();
        foreach ($res as $bit) {
            if ($bit == 0) {
                return false;
            }
        }
        return true;
    }
}