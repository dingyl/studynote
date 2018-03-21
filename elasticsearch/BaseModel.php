<?php
require_once "utils.php";

//id 不允许修改
abstract class BaseModel
{
    //定义表的属性字段  id默认就有
    public static $attributes = [];

    public $error_reason;

    protected $_exec_level = 0;

    protected $_is_new_record = true;

    protected $_attributes = [];

    protected $_old_attributes = [];

    protected $validate_messages = [
        'required' => '%s不能为空',
        'email' => '%s邮箱格式不正确',
        'uniqid' => '%s为%s的已经存在',
        'integer' => '%s必须是整形',
        'string' => '%s必须是字符串',
        'equal' => '%s不等于%s',
        'telphone' => '%s电话号码格式不正确',
        'mobile' => '%s手机号码格式不正确',
        'url' => '%surl链接格式不正确',
        'max' => '%s不能大于%d',
        'max_len' => '%s不能大于%d个字符',
        'min' => '%s不能小于%d',
        'min_len' => '%s不能小于%d个字符',
    ];

    //require,integer,string,email,function,equal
    public $rules = [
//        [['certification_image_id'], 'required'],
//        [[''], 'integer', 'max' => '', 'min' => ''],
//        [[], 'string', 'max_len' => '', 'min_len' => ''],
//        [[], 'email',],
        //函数验证规则，这个函数是要在类中定义的
//        [[], 'function', 'func_name', 'message' => ''],
//        ['old_password', 'equal', 'new_password'],
    ];

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }

        //按照下划线转驼峰的方式查找相对应的方法 isOpen()  is_open属性调用
        $tname = underLineString2Camel($name);
        $method_name = strtolower($tname[0]) . substr($tname, 1);
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }

        //developer  getDeveloper
        $method_name = 'get' . $tname;
        if (is_callable([$this, $method_name]) && method_exists($this, $method_name)) {
            return $this->$method_name();
        }


        $reflect = new \ReflectionClass(get_called_class());
        //状态常量文本转换
        if (substr($name, -4) == "text") {
            $tname = explode('_', $name)[0];
            $uppername = strtoupper($tname);
            if ($static_props = $reflect->getStaticProperties()) {
                if (isset($static_props[$uppername])) {
                    return $static_props[$uppername][$this->$tname];
                }
            }
        }

        //数据表关联处理
        $_name = '_' . $name;
        if ($reflect->hasProperty($_name)) {
            $relate = $reflect->getProperty($_name);
            if ($relate->isPrivate()) {
                $mark = $relate->getDocComment();
                preg_match('/@(.*?)[\r|\n]/', $mark, $match);
                list($type, $class) = explode(' ', $match[1]);
                if (strtolower($type) == 'type') {
                    $field = $name . "_id";
                    return $class::findById($this->$field);
                }
            }
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->_attributes[$name]);
    }

    public function __toString()
    {
        return json_encode($this->_attributes, JSON_UNESCAPED_UNICODE);
    }

    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;

        if ($scope === '' && !empty($data)) {
            $data = array_filter($data,function($k){
                return in_array($k,static::$attributes);
            },ARRAY_FILTER_USE_KEY);
            $this->setAttributes($data);
            return true;
        } elseif (isset($data[$scope])) {
            $data[$scope] = array_filter($data[$scope],function($k){
                return in_array($k,static::$attributes);
            },ARRAY_FILTER_USE_KEY);
            $this->setAttributes($data[$scope]);
            return true;
        }

        return false;
    }

    public function toJson()
    {
        return array_merge($this->_attributes, $this->mergeJson());
    }

    public function mergeJson()
    {
        return [];
    }

    public function setAttributes($data)
    {
        $this->_attributes = $data;
    }

    public function setOldAttributes($data)
    {
        $this->_old_attributes = $data;
    }

    public static function tableName()
    {
        return '';
    }

    public static function aliasName()
    {
        return '';
    }

    public static function formName(){
        $table_name = static::tableName();
        return ucwords(underLineString2Camel($table_name));
    }

    public function hasAttributes($name)
    {
        $temp = static::$attributes;
        $temp[] = 'id';
        return in_array($name, $temp);
    }


    public static function findById($id)
    {
        return null;
    }

    public static function __callStatic($method, $params)
    {
        $prefix = substr($method, 0, 6);
        if ($prefix == 'findBy') {
            $name = substr($method, 6);
            $underline_name = camel2UnderLineString($name);
            return static::findAll([$underline_name => $params[0]]);
        }

        $prefix = substr($method, 0, 9);
        if ($prefix == 'findOneBy') {
            $name = substr($method, 9);
            $underline_name = camel2UnderLineString($name);
            return static::findOne([$underline_name => $params[0]]);
        }
    }


    public function validate()
    {
        $rules = $this->rules;
        $validate_messages = $this->validate_messages;
        $attributeLabels = $this->attributeLabels;
        foreach ($rules as $rule) {
            $validate_type = $rule[1];
            $fields = $rule[0];
            switch ($validate_type) {
                case 'required' :
                    foreach ($fields as $field) {
                        if (!$this->$field) {
                            $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;
                            $this->error_reason = sprintf($validate_messages['required'], $filed_name);
                            return false;
                        }
                    }
                    break;
                case 'string' :
                    foreach ($fields as $field) {
                        if ($this->$field) {
                            $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;
                            if (isset($rule['max_len']) && strlen($this->$field) > $rule['max_len']) {
                                $this->error_reason = sprintf($validate_messages['max_len'], $filed_name, $rule['max_len']);
                                return false;
                            }
                            if (isset($rule['min_len']) && strlen($this->$field) < $rule['min_len']) {
                                $this->error_reason = sprintf($validate_messages['min_len'], $filed_name, $rule['min_len']);
                                return false;
                            }
                        }
                    }
                    break;
                case 'uniqid' :
                    foreach ($fields as $field) {
                        if ($this->isNewRecord() && $this->$field) {
                            $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;

                            $t_funcname = 'findOneBy' . $field;

                            if (self::$t_funcname($this->$field)) {
                                $this->error_reason = sprintf($validate_messages['uniqid'], $filed_name, $this->$field);
                                return false;
                            }
                        }
                    }
                    break;
                case 'integer' :
                    foreach ($fields as $field) {
                        $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;
                        if(isInteger($this->$field)){
                            $this->$field = intval($this->$field);
                            if ($this->$field) {

                                if (isset($rule['max']) && $this->$field > $rule['max']) {
                                    $this->error_reason = sprintf($validate_messages['max'], $filed_name, $rule['max']);
                                    return false;
                                }
                                if (isset($rule['min']) && $this->$field < $rule['min']) {
                                    $this->error_reason = sprintf($validate_messages['min'], $filed_name, $rule['min']);
                                    return false;
                                }
                            }
                        }else{
                            $this->error_reason = sprintf($validate_messages['integer'], $filed_name);
                            return false;
                        }
                    }
                    break;
                case 'float' :
                    foreach ($fields as $field) {
                        $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;
                        if(isInteger($this->$field)){
                            $this->$field = intval($this->$field);
                            if ($this->$field) {

                                if (isset($rule['max']) && $this->$field > $rule['max']) {
                                    $this->error_reason = sprintf($validate_messages['max'], $filed_name, $rule['max']);
                                    return false;
                                }
                                if (isset($rule['min']) && $this->$field < $rule['min']) {
                                    $this->error_reason = sprintf($validate_messages['min'], $filed_name, $rule['min']);
                                    return false;
                                }
                            }
                        }else{
                            $this->error_reason = sprintf($validate_messages['float'], $filed_name);
                            return false;
                        }
                    }
                    break;
                case 'email' :
                    foreach ($fields as $field) {
                        if ($this->$field && !isEmail($this->$field)) {
                            $filed_name = isset($attributeLabels[$field]) ? $attributeLabels[$field] : $field;
                            $this->error_reason = sprintf($validate_messages['email'], $filed_name);
                            return false;
                        }
                    }
                    break;
                case 'equal' :
                    $left_field = $fields;
                    $right_field = $rule[2];
                    if ($this->$left_field != $this->$right_field) {
                        $left_field_name = isset($attributeLabels[$left_field]) ? $attributeLabels[$left_field] : $left_field;
                        $right_field_name = isset($attributeLabels[$right_field]) ? $attributeLabels[$right_field] : $right_field;
                        $this->error_reason = sprintf($validate_messages['equal'], $left_field_name, $right_field_name);
                        return false;
                    }
                    break;
                case 'function' :
                    $func_name = $rule[2];
                    foreach ($fields as $field) {
                        if ($this->$field && !$this->$func_name()) {
                            return false;
                        }
                    }
                    break;
            }
        }
        return true;
    }

    //判断是否是新添加的记录
    public function isNewRecord()
    {
        return $this->_is_new_record;
    }

    public function save()
    {
        if ($this->_attributes) {
            $this->_exec_level++;
            if ($this->_exec_level == 1) {
                $this->beforeSave();
            }
            $this->_exec_level--;
            if ($this->isNewRecord()) {
                $this->_exec_level++;
                if ($this->_exec_level == 1) {
                    $this->beforeInsert();
                }
                $this->_exec_level--;
                $status = $this->insert();
                $this->_exec_level++;
                if ($this->_exec_level == 1) {
                    $this->afterInsert();
                }
                $this->_exec_level--;

                if ($status) {
                    $this->_is_new_record = false;
                }
            } else {
                $this->_exec_level++;
                if ($this->_exec_level == 1) {
                    $this->beforeUpdate();
                }
                $this->_exec_level--;
                $status = $this->update();
                $this->_exec_level++;
                if ($this->_exec_level == 1) {
                    $this->afterUpdate();
                }
                $this->_exec_level--;
            }

            if ($status) {
                $this->setOldAttributes($this->_attributes);
            }

            $this->_exec_level++;
            if ($this->_exec_level == 1) {
                $this->afterSave();
            }
            $this->_exec_level--;
        } else {
            $this->error_reason = '数据为空';
            $status = false;
        }
        return $status;
    }

    public function beforeSave()
    {
        if ($this->isNewRecord() && $this->hasAttributes('created_at')) {
            $this->created_at = time();
        }
        if ($this->hasAttributes('updated_at')) {
            $this->updated_at = time();
        }
        return true;
    }

    public function afterSave()
    {

    }

    public function insert()
    {
        return true;
    }

    public function beforeInsert()
    {

    }

    public function afterInsert()
    {

    }

    public function update()
    {
        return true;
    }

    public function beforeUpdate()
    {

    }

    public function afterUpdate()
    {

    }

    public function delete()
    {
        $this->_exec_level++;
        if ($this->_exec_level == 1) {
            $this->beforeDelete();
        }
        $this->_exec_level--;

        $status = $this->realDelete();
        $this->_exec_level++;
        if ($this->_exec_level == 1) {
            $this->afterDelete();
        }
        $this->_exec_level--;

        return $status;
    }

    public function realDelete()
    {
        return true;
    }

    public function beforeDelete()
    {

    }

    public function afterDelete()
    {

    }
}