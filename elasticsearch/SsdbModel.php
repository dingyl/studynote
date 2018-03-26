<?php
require_once "BaseModel.php";

class SsdbModel extends BaseModel
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
        return ['127.0.0.1:8888'];
    }

    protected static function writeClient()
    {
        if (!static::$writer instanceof static) {
            $hosts = self::hosts();
            $host_info = $hosts[0];
            $host = explode(':', $host_info)[0];
            $port = explode(':', $host_info)[1];
            $ssdb = new SSDB($host, $port);
            static::$writer = $ssdb;
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
            $ssdb = new SSDB($host, $port);
            static::$reader = $ssdb;
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
        $ssdb = self::writeClient();
        if ($expire) {
            return $ssdb->setx($key, $value, $expire);
        } else {
            return $ssdb->set($key, $value);
        }
    }

    public static function setnx($key, $value)
    {
        $ssdb = self::writeClient();
        return $ssdb->setnx($key, $value);
    }

    public static function get($key)
    {
        $ssdb = self::readerClient();
        return $ssdb->get($key);
    }

    public static function del($key)
    {
        $ssdb = self::writeClient();
        return $ssdb->del($key);
    }

    public static function incr($key, $dept = 1)
    {
        $ssdb = self::writeClient();
        return $ssdb->incr($key, $dept);
    }

    public static function decr($key, $dept = 1)
    {
        $ssdb = self::writeClient();
        return $ssdb->decr($key, $dept);
    }

    public static function keys()
    {
        $ssdb = self::readerClient();
        $keys = [];
        $keys['simple_keys'] = $ssdb->keys('', '', PHP_INT_MAX);
        $keys['z_keys'] = $ssdb->zlist('', '',PHP_INT_MAX);
        $keys['h_keys'] = $ssdb->hlist('', '',PHP_INT_MAX);
        return $keys;
    }

    public static function expire($key, $time)
    {
        $ssdb = self::writeClient();
        return $ssdb->expire($key, $time);
    }

    public static function hMset($key, $data)
    {
        $ssdb = self::writeClient();
        return $ssdb->multi_hset($key, $data);
    }

    public static function hGetAll($key)
    {
        $ssdb = self::readerClient();
        $data = $ssdb->hgetall($key);
        return $data ? $data : [];
    }

    public static function hDel($key){
        return self::hclear($key);
    }


    public static function zAdd($key, $value, $sort)
    {
        $ssdb = self::writeClient();
        return $ssdb->zset($key, $sort, $value);
    }

    public static function zContains($key, $value)
    {
        $ssdb = self::readerClient();
        return $ssdb->zexists($key, $value);
    }

    public static function zCard($key)
    {
        $ssdb = self::readerClient();
        return $ssdb->zcount($key, 0, PHP_INT_MAX);
    }

    public static function zRange($key, $start, $limit)
    {
        $ssdb = self::readerClient();
        $data = $ssdb->zrange($key, $start, $limit);
        return $data ? $data : [];
    }

    public static function zGetAll($key)
    {
        $ssdb = self::readerClient();
        $data = $ssdb->zscan($key, '', '', '', PHP_INT_MAX);
        return $data ? $data : [];
    }

    public static function zDel($key, $value)
    {
        $ssdb = self::writeClient();
        return $ssdb->zdel($key, $value);
    }

    public static function info(){
        $ssdb = self::readerClient();
        return $ssdb->info();
    }
}