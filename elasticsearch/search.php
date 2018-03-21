<?php
require 'ElasticModel.php';
require 'RedisModel.php';
require 'SsdbModel.php';
echo "<pre>";
$keys = SsdbModel::keys();
//print_r($keys);
//
//$category = new SsdbModel();
//$category->name = 'hello';
//$category->age = 23;
//$category->save();
//
//
$models = SsdbModel::findAll(['age'=>23],'id desc');

foreach($models as $model){
    print_r($model->toJson());
}