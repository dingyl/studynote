<?php
abstract class Product{
    public $prop=null;
    public function __construct(){
        var_dump($this->prop);
    }
}

class DaProduct extends Product{
    public $prop="da";
}


class DbProduct extends Product{
    public $prop="db";
}


class Factory{
    static public function create($type){
        $product = null;
        switch($type){
            case 'Da':
                $product = new DaProduct();
                break;
            default:
                break;
        }
        return $product;
    }
}

$da = Factory::create('Da');