<?php

namespace models;

/**
 * 所有表模型字段需要主动声明定义
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * Class BaseModel
 * @package yii\base
 */
class BaseModel
{

    public $_attributes = [];

    protected $_is_new_record;

    protected static $_db = null;

    public function __construct()
    {
        $this->_is_new_record = true;
    }

    public static function tableName()
    {
        $reflect = new \ReflectionClass(get_called_class());
        $table_name = camel2UnderLineString($reflect->getShortName());
        return $table_name;
    }

    public static function getDb()
    {
        if (self::$_db === null) {
            $dsn = DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
            self::$_db = new \PDO($dsn, DB_USER, DB_PASSWORD);
        }
        return self::$_db;
    }

    public function query($sql)
    {
        $db = self::getDb();
        return $db->query($sql);
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            return false;
        }
    }

    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    public function __toString()
    {
        return json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->getAttributes();
    }

    //判断是否是新添加的记录
    public function isNewRecord()
    {
        return $this->_is_new_record;
    }

    public function setAttributes($data)
    {
        foreach ($data as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        return $this->_attributes ? $this->_attributes : [];
    }

    public function hasAttribute($name)
    {
        return true;
    }

    public function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    public function setAttribute($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        }
    }

    public static function findAll()
    {
        $db = self::getDb();
        $sql = 'select * from ' . self::tableName();
        info('find all sql ', $sql);
        $query = $db->query($sql);
        $rows = $query->fetchAll();
        $models = [];
        foreach ($rows as $row) {
            foreach ($row as $field => $value) {
                if (is_integer($field)) {
                    unset($row[$field]);
                }
            }
            $model = self::createObject($row);
            $model->_is_new_record = false;
            $models[] = $model;
        }
        return $models;
    }

    public static function findById($id)
    {
        $db = self::getDb();
        $sql = 'select * from ' . self::tableName() . ' where id = ' . $id;
        info('find sql ', $sql);
        $query = $db->query($sql);
        $rows = $query->fetchAll();
        if (count($rows)) {
            $data = $rows[0];
            foreach ($data as $field => $value) {
                if (is_integer($field)) {
                    unset($data[$field]);
                }
            }
            $model = self::createObject($data);
            $model->_is_new_record = false;
            return $model;
        }
        return null;
    }

    public function save()
    {
        if ($this->isNewRecord()) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    public function create()
    {
        $db = self::getDb();
        $attributes = $this->_attributes;
        $fields = [];
        $values = [];
        $binds = [];
        foreach ($attributes as $field => $value) {
            $fields[] = '`' . $field . '`';
            $values[] = '\'' . $value . '\'';
//            $param = ':' . $field;
//            $values[] = '\'' . $param . '\'';
//            $binds[$param] = $value;
        }
        $sql = 'insert into ' . static::tableName() . '(' . implode(', ', $fields) . ') values (' . implode(', ', $values) . ') ';
        info('create sql ', $sql, $binds);
        $status = $db->exec($sql);
//        $execute = $db->prepare($sql);
//        $status = $execute->execute($binds);
        info('status', $status);
        if ($status) {
            $this->_is_new_record = false;
            return $db->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $db = self::getDb();
        $attributes = $this->_attributes;
        $fields = [];
        $binds = [];
        foreach ($attributes as $field => $value) {
//            $param = ':' . $field;
            $param = '\'' . $value . '\'';
            $fields[] = $field . ' = ' . $param;
//            $binds[$param] = $value;
        }
        $sql = 'update ' . static::tableName() . ' set ' . implode(', ', $fields) . ' where id = ' . $this->id;
        info('update sql ', $sql);
        $status = $db->exec($sql);
//        $execute = $db->prepare($sql);
//        $status = $execute->execute($binds);
        info('status', $status);
        return $status;
    }

    public function delete()
    {
        $db = self::getDb();
        $sql = 'delete from `' . static::tableName() . '` where id = ' . $this->id;
        info('delete sql ', $sql, 'get_called_class', get_called_class());
        return $db->exec($sql);
    }

    public static function createObject($data)
    {
        $model = new static();
        $model->setAttributes($data);
        return $model;
    }
}