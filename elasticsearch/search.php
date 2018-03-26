<?php
require 'ElasticModel.php';
require 'RedisModel.php';
require 'SsdbModel.php';
echo "<pre>";
$keys = SsdbModel::keys();
print_r($keys);
//print_r(SsdbModel::info());