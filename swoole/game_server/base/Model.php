<?php

namespace base;

use base\structs\RedisHash;

abstract class Model
{
    public $id;
    public $data_hash;

    public function __construct($id)
    {
        $this->id = $id;
        $this->data_hash = $this->getDataHash($id);
    }

    abstract function getDataHashKey($id);

    public function getDataHash($id)
    {
        $data_hash_key = $this->getDataHashKey($id);
        return new RedisHash($data_hash_key);
    }

    public static function findById($id)
    {
        $class = get_called_class();
        $model = new $class($id);
        if ($model->exists()) {
            return $model;
        } else {
            return null;
        }
    }

    public function setData($data)
    {
        $this->data_hash->mSet($data);
    }

    public function detail()
    {
        return $this->data_hash->detail();
    }

    public function set($name, $value)
    {
        $this->data_hash->set($name, $value);
    }

    public function get($name)
    {
        return $this->data_hash->get($name);
    }

    public function __get($name)
    {
        $value = $this->data_hash->get($name);
        return $value ? $value : '';
    }

    public function incr($name)
    {
        $this->data_hash->incr($name);
    }

    public function decr($name)
    {
        $this->data_hash->decr($name);
    }

    public function delete()
    {
        $this->data_hash->delete();
    }

    public function exists()
    {
        return $this->data_hash->exists();
    }
}