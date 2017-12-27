<?php
abstract class AbstractCache{
    protected $db;
    protected static $ins;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function set($key, $value)
    {
        $this->db->set($key, $value);
        return $this;
    }

    public function expire($key, $time)
    {
        $this->db->expire($key, $time);
        return $this;
    }

    public function ttl($key)
    {
        return $this->db->ttl($key);
    }

    public function get($key)
    {
        return $this->db->get($key);
    }

    public function exists($key)
    {
        return $this->db->exists($key);
    }

    /**
     * @param $config   'host:port,host1:port1'
     */
    public static function getReaderCache($config)
    {
        $servers = explode(',',$config);
        $index = array_rand($servers);
        $server = explode(':',$servers[$index]);
        return static::getIns($server[0],$server[1]);
    }

    public static function getWriterCache($config)
    {
        $servers = explode(',',$config);
        $server = explode(':',$servers[0]);
        return static::getIns($server[0],$server[1]);
    }
}