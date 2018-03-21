<?php
require_once "BaseModel.php";

class RedisModel extends BaseModel
{
    protected static $reader;

    protected static $writer;

    protected static function prefixKey()
    {
        return self::tableName();
    }

    protected static function idKey()
    {
        return self::prefixKey() . ':id';
    }

    protected static function idListKey()
    {
        return self::prefixKey();
    }

    protected static function getKeyById($id)
    {
        return self::prefixKey() . ':id:' . $id;
    }

    protected function getKey()
    {
        return self::getKeyById($this->id);
    }

    //子类必须设置
    public static function tableName()
    {
        return 'category';
    }

    public static function hosts()
    {
        return ['127.0.0.1:6379'];
    }

    protected static function writeClient()
    {
        if (!static::$writer instanceof static) {
            $hosts = self::hosts();
            $host_info = $hosts[0];
            $host = explode(':', $host_info)[0];
            $port = explode(':', $host_info)[1];
            $redis = new Redis();
            $redis->connect($host, $port);
            static::$writer = $redis;
        }
        return static::$writer;
    }

    protected static function readerClient()
    {
        if (!static::$reader instanceof static) {
            $hosts = self::hosts();
            $host_info = $hosts[array_rand($hosts, 1)];
            $host = explode(':', $host_info)[0];
            $port = explode(':', $host_info)[1];
            $redis = new Redis();
            $redis->connect($host, $port);
            static::$reader = $redis;
        }
        return static::$reader;
    }

    public function insert()
    {
        $id_key = self::idKey();
        $id_list_key = self::idListKey();
        $this->id = self::incr($id_key);
        self::zAdd($id_list_key, $this->id, $this->id);
        $key = $this->getKey();
        return self::hMset($key, $this->_attributes);
    }

    public function update()
    {
        $this->id = $this->_old_attributes['id'];
        $key = $this->getKey();
        return self::hMset($key, $this->_attributes);
    }

    public function realDelete()
    {
        $key = $this->getKey();
        $id_list_key = self::idListKey();
        self::zDel($id_list_key,$this->id);
        return self::hDel($key);
    }

    public static function findById($id)
    {
        $key = self::getKeyById($id);
        $data = self::hGetAll($key);
        if ($data) {
            $_this = new self();
            $_this->_is_new_record = false;
            $_this->setAttributes($data);
            $_this->setOldAttributes($data);
            return $_this;
        } else {
            return null;
        }
    }

    public static function findOne($cond = [], $order = '')
    {
        $data = self::findAll($cond, $order);
        return isset($data[0]) ? $data[0] : null;
    }

    //单条件查询,单字段排序
    public static function findAll($cond = [], $order = '')
    {
        $order = trim($order);
        $id_list_key = self::idListKey();
        $ids = self::zGetAll($id_list_key);
        $data = [];
        $temp_data = [];
        foreach ($ids as $id) {
            $model = self::findById($id);
            if ($model) {
                $status = true;
                foreach ($cond as $field => $value) {
                    if ($model->$field != $value) {
                        $status = false;
                        break;
                    }
                }
                if ($status) {
                    $data[] = $model;
                    if ($order) {
                        $temp_data[] = $model->_attributes;
                    }
                }
            }
        }

        if ($temp_data) {
            $temp_data = rectSort($temp_data, $order);
            foreach ($temp_data as $k => $row) {
                $data[$k]->setAttributes($row);
            }
        }
        return $data;
    }


    public static function count($cond = [])
    {
        if ($cond) {
            $data = self::findAll($cond);
            $count = count($data);
        } else {
            $id_list_key = self::idListKey();
            $count = self::zCard($id_list_key);
        }
        return $count;
    }

    public static function findPagination($cond = [], $order = '', $page = 1, $per_page = 10)
    {
        $data = self::findAll($cond, $order);
        $start = ($page - 1) * $per_page;
        return array_slice($data, $start, $per_page);
    }

    public static function set($key, $value, $expire = null)
    {
        $redis = self::writeClient();
        if ($expire) {
            return $redis->set($key, $value, $expire);
        } else {
            return $redis->set($key, $value);
        }
    }

    public static function expire($key,$expire){
        $redis = self::writeClient();
        return $redis->expire($key, $expire);
    }

    public static function setnx($key, $value)
    {
        $redis = self::writeClient();
        return $redis->setnx($key, $value);
    }

    public static function get($key)
    {
        $redis = self::readerClient();
        return $redis->get($key);
    }

    public static function del($key)
    {
        $redis = self::writeClient();
        return $redis->delete($key);
    }

    public static function incr($key, $dept = 1)
    {
        $redis = self::writeClient();
        return $redis->incrBy($key, $dept);
    }

    public static function decr($key, $dept = 1)
    {
        $redis = self::writeClient();
        return $redis->decrBy($key, $dept);
    }

    public static function keys()
    {
        $redis = self::readerClient();
        return $redis->keys('*');
    }

    public static function hMset($key, $data)
    {
        $redis = self::writeClient();
        return $redis->hMset($key, $data);
    }

    public static function hGetAll($key)
    {
        $redis = self::readerClient();
        $data = $redis->hGetAll($key);
        return $data ? $data : [];
    }

    public static function hDel($key){
        return self::del($key);
    }


    public static function zAdd($key, $value, $sort)
    {
        $redis = self::writeClient();
        return $redis->zAdd($key, $sort, $value);
    }

    public static function zContains($key, $value)
    {
        return in_array($value, self::zGetAll($key));
    }

    public static function zCard($key)
    {
        $redis = self::readerClient();
        return $redis->zCard($key);
    }

    public static function zRange($key, $start, $limit)
    {
        $redis = self::readerClient();
        $data = $redis->zRevRange($key, $start, $start + $limit - 1);
        return $data ? $data : [];
    }

    public static function zGetAll($key)
    {
        $redis = self::readerClient();
        $data = $redis->zRevRange($key, 0, -1);
        return $data ? $data : [];
    }

    public static function zDel($key, $value)
    {
        $redis = self::writeClient();
        return $redis->zDelete($key, $value);
    }
}