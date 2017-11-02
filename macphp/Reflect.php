<?php
class Demo{
    public $name;
    protected $age;
    private $sex;

    /**
     * @return mixed
     */
    public function getName(){
        return $this->name;
    }

    protected function getAge(){
        return $this->age;
    }

    private function getSex(){
        return $this->sex;
    }
}

$cname = 'Demo';
$reflect = new ReflectionClass($cname);
//获取属性
$prop = $reflect->getProperties();
//获取所有方法
$method = $reflect->getMethods();
$props = [];
$methods = [];
foreach ($method as $v){
    //获取注释
    $mark = $v->getDocComment();
    array_push($methods,$v->class.'-'.$v->name.' mark:'.$mark);
}
foreach ($prop as $v){
    array_push($props,$v->class.'-'.$v->name);
}
print_r($methods);
print_r($props);

//过程式
//获取所有方法
$methods = get_class_methods('ReflectionClass');
//获取属性
$props = get_class_vars('ReflectionClass');
