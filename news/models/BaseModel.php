<?php

namespace models;

/**
 * Class BaseModel
 * @package models
 */
class BaseModel
{

    /**
     * @var array 对象属性
     */
    protected $_attributes = [];

    /**
     * @var bool 对象是否是新创建标识
     */
    protected $_is_new_record;

    /**
     * @var null 数据库连接句柄
     */
    protected static $_db = null;

    /**
     * 主键字段名称
     */
    const PRIMARY_FIELD = 'id';

    /**
     * 表字段定义
     * @return array
     */
    public static function fields()
    {
        return [];
    }

    public function __construct()
    {
        $this->_is_new_record = true;
    }

    /**
     * 表明获取
     * @return string
     * @throws \ReflectionException
     */
    public static function tableName()
    {
        $reflect = new \ReflectionClass(get_called_class());
        $table_name = camel2UnderLineString($reflect->getShortName());
        return $table_name;
    }

    /**
     * 获取数据库连接
     * @return null|\PDO
     */
    public static function getDb()
    {
        if (self::$_db === null) {
            $dsn = DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
            self::$_db = new \PDO($dsn, DB_USER, DB_PASSWORD);
        }
        return self::$_db;
    }

    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
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

    /**
     * 判断是否是新添加的记录
     * @return bool
     */
    public function isNewRecord()
    {
        return $this->_is_new_record;
    }

    /**
     * 设置属性
     * @param $data
     * @param bool $flag
     */
    public function setAttributes($data, $flag = false)
    {
        foreach ($data as $name => $value) {
            $this->setAttribute($name, $value, $flag);
        }
    }

    /**
     * 获取所有属性值
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes ? $this->_attributes : [];
    }

    /**
     * 判断是否有某个属性
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return in_array($name, static::fields());
    }

    /**
     * 获取某个属性值
     * @param $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * 设置属性值
     * @param $name
     * @param $value
     * @param bool $flag
     * @return bool
     */
    public function setAttribute($name, $value, $flag = false)
    {
        if ($this->hasAttribute($name)) {
            if ($flag === true || $name != self::PRIMARY_FIELD) {
                $this->_attributes[$name] = $value;
            }
            return true;
        }
        return false;
    }

    public static function findAll()
    {
        $db = self::getDb();
        $sql = 'select ' . implode(', ', static::fields()) . ' from ' . self::tableName();
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

    /**
     * 根据id进行查找
     * @param $id
     * @return BaseModel|null
     * @throws \ReflectionException
     */
    public static function findById($id)
    {
        try {
            $db = self::getDb();
            $sql = 'select ' . implode(', ', static::fields()) . ' from ' . self::tableName() . ' where id = ' . $id;
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
        } catch (\PDOExecption $e) {
            info('Exce', $e->getMessage());
            return null;
        };
    }

    /**
     * 保存操作
     * @return bool|string
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->isNewRecord()) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    /**
     * @return bool|string
     * @throws \ReflectionException
     */
    public function create()
    {
        try {
            $db = self::getDb();
            $attributes = $this->_attributes;
            $fields = [];
            $values = [];
            $binds = [];
            foreach ($attributes as $field => $value) {
                $fields[] = '`' . $field . '`';
                $values[] = ':' . $field;
                $binds[$field] = $value;
            }
            $sql = 'insert into ' . static::tableName() . '(' . implode(', ', $fields) . ') values (' . implode(', ', $values) . ') ';
            info('create sql ', $sql, $binds);
            $execute = $db->prepare($sql);
            $status = $execute->execute($binds);
            info('status', $status);
            if ($status) {
                $this->_is_new_record = false;
                return $db->lastInsertId();
            }
        } catch (\PDOException $e) {
            info('create Exce', $e->getMessage());
            return false;
        }
    }

    /**
     * 数据更新
     * @return bool
     * @throws \ReflectionException
     */
    public function update()
    {
        try {
            $db = self::getDb();
            $attributes = $this->_attributes;
            $fields = [];
            $binds = [];
            foreach ($attributes as $field => $value) {
                $fields[] = $field . ' = ' . ':' . $field;
                $binds[$field] = $value;
            }
            $sql = 'update ' . static::tableName() . ' set ' . implode(', ', $fields) . ' where id = :id';
            info('update sql ', $sql);
            $execute = $db->prepare($sql);
            $binds['id'] = $this->id;
            $status = $execute->execute($binds);
            info('status', $status);
            return $status;
        } catch (\PDOException $e) {
            info('update Exce', $e->getMessage());
            return false;
        }
    }

    /**
     * 数据删除
     * @return bool
     * @throws \ReflectionException
     */
    public function delete()
    {
        try {
            $db = self::getDb();
            $sql = 'delete from ' . static::tableName() . ' where id = :id';
            $execute = $db->prepare($sql);
            $status = $execute->execute(['id' => $this->id]);
            return $status;
        } catch (\PDOException $e) {
            info('delete Exce', $e->getMessage());
            return false;
        }
    }

    /**
     * 使用数组数据创建对象
     * @param array $data
     * @return BaseModel
     */
    public static function createObject($data)
    {
        $model = new static();
        $model->setAttributes($data, true);
        return $model;
    }
}