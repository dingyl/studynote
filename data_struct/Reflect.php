<?php

/**
 * Class Demo
 */
class Demo{

    /**
     * name
     * @var
     */
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

class Reflect{
    protected $class_name;
    protected static $ins = null;
    final protected function __construct(){}

    public static function getClass($class_name){
        if(! self::$ins instanceof self){
            self::$ins = new self();
        }
        (self::$ins)->class_name = $class_name;
        return (self::$ins)->init();
    }

    public function init(){
        $temp = [];
        $cls = new ReflectionClass($this->class_name);
        $temp['name'] = $this->class_name;
        $temp['mark'] = $cls->getDocComment();
        foreach ($cls->getMethods() as $method){
            $temp['methods'][] = ['name'=>$method->name,'mark'=>$method->getDocComment()];
        }

        foreach ($cls->getProperties() as $property){
            $temp['propertys'][] = ['name'=>$property->name,'mark'=>$property->getDocComment()];
        }
        return $temp;
    }

    final protected function __clone(){}

}

print_r(Reflect::getClass("Demo"));