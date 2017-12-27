<?php
require_once __DIR__.'/SsdbCache.php';
class XRedis extends AbstractCache implements CacheInterface
{
    protected $ssdb;
    protected $ssdb_config;

    public function setSsdb($config){
        $this->ssdb_config = $config;
        $this->ssdb = SsdbCache::getWriterCache($this->ssdb_config);
    }

    public static function getIns($host, $port)
    {
        if (!static::$ins instanceof static) {
            static::$ins = new static();
            static::$ins->db = new Redis();
        }
        if(static::$ins->db){
            static::$ins->db->close();
        }
        static::$ins->db->connect($host, $port);
        return self::$ins;
    }

    public function set($key, $value)
    {
        $this->db->set($key, $value);
        if($this->ssdb){
            $this->ssdb->set($key, $value);
        }
        return $this;
    }

    public function get($key)
    {
        $value = $this->db->get($key);
        if(!$value && $this->ssdb){
            $this->set($key,$value);
        }
        return $value;
    }

    public function type($key)
    {
        return $this->db->type($key);
    }

    public function delete($key)
    {
        $this->db->delete($key);
        if($this->ssdb){
            $this->ssdb->delete($key);
        }
        return $this;
    }

    public function keys()
    {
        return $this->db->keys('*');
    }

    public function incr($key, $dept = 1)
    {
        $this->db->incrBy($key, $dept);
        if($this->ssdb){
            $this->ssdb->incrBy($key, $dept);
        }
        return $this;
    }

    public function decr($key, $dept)
    {
        $this->db->decrBy($key, $dept);
        if($this->ssdb){
            $this->ssdb->decrBy($key, $dept);
        }
        return $this;
    }

    public function sadd($key, $value)
    {
        $this->db->sAdd($key, $value);
        if($this->ssdb){
            $this->ssdb->sadd($key, $value);
        }
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
        if($this->ssdb){
            $this->ssdb->sdel($key, $value);
        }
        return $this;
    }

    public function sgetall($key)
    {
        $data = $this->db->sGetMembers($key);
        if(!$data && $this->ssdb){
            $data = $this->ssdb->sgetall($key);
            if($data){
                foreach($data as $value){
                    $this->sadd($key,$value);
                }
            }
        }
        return $data;
    }

    public function sclear($key)
    {
        $argv = array_merge([$key],$this->sgetall($key));
        call_user_func_array([$this->db,'sRemove'],$argv);
        if($this->ssdb){
            $this->ssdb->sclear($key);
        }
        return $this;
    }

    public function zadd($key, $value, $sort)
    {
        $this->db->zAdd($key, $sort, $value);
        if($this->ssdb){
            $this->ssdb->zadd($key, $value, $sort);
        }
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
        $data = $this->db->zRevRange($key, $start, $start+$limit-1);
        if(!$data && $this->ssdb){
            $data = $this->ssdb->zrange($key, $start, $limit);
            if($data){
                foreach($data as $value=>$score){
                    $this->zadd($key,$value,$score);
                }
            }
        }
        return $data;
    }

    public function zgetall($key)
    {
        $data = $this->db->zRevRange($key, 0, -1);
        if(!$data && $this->ssdb){
            $data = $this->ssdb->zgetall($key);
            if($data){
                foreach($data as $value=>$score){
                    $this->zadd($key,$value,$score);
                }
            }
        }
        return $data;
    }

    public function zclear($key)
    {
        $this->db->zDeleteRangeByRank($key,'','');
        if($this->ssdb){
            $this->ssdb->zclear($key);
        }
        return $this;
    }

    public function zdel($key, $value)
    {
        $this->db->zDelete($key,$value);
        if($this->ssdb){
            $this->ssdb->zdel($key, $value);
        }
        return $this;
    }

    public function hmset($key, $info)
    {
        $this->db->hMset($key, $info);
        if($this->ssdb){
            $this->ssdb->hmset($key,$info);
        }
        return $this;
    }

    public function hset($key, $field, $value)
    {
        $this->db->hSet($key, $field, $value);
        if($this->ssdb){
            $this->ssdb->hset($key, $field, $value);
        }
        return $this;
    }

    public function hgetall($key)
    {
        $data = $this->db->hGetAll($key);
        if(!$data && $this->ssdb){
            $data = $this->ssdb->hgetall($key);
            if($data){
                $this->hmset($key,$data);
            }
        }
        return $data;
    }

    public function hget($key, $field)
    {
        $value = $this->db->hGet($key, $field);
        if(!$value && $this->ssdb){
            $value = $this->ssdb->hget($key, $field);
            if($value){
                $this->hset($key,$field,$value);
            }
        }
        return $value;
    }

    public function hclear($key)
    {
        $argv = array_merge([$key],$this->hgetall($key));
        call_user_func_array([$this->db,'hDel'],$argv);
        if($this->ssdb){
            $this->ssdb->hclear($key);
        }
        return $this;
    }

    public function hdel($key, $field)
    {
        $this->db->hDel($key, $field);
        if($this->ssdb){
            $this->ssdb->hdel($key, $field);
        }
        return $this;
    }

    public function flushdb()
    {
        $this->db->flushDB();
        if($this->ssdb){
            $this->ssdb->flushdb();
        }
        return $this;
    }
}