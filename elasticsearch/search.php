<?php
require 'ElasticModel.php';
echo "<pre>";
$obj = ElasticModel::count();
print_r($obj);