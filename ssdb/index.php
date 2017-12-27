<?php
require 'XRedis.php';
$redis_config = 'localhost:6379';
$ssdb_config = 'localhost:8888';
$redis = XRedis::getWriterCache($redis_config);
$redis->setSsdb($ssdb_config);
$ssdb = SsdbCache::getWriterCache($ssdb_config);

$name = "testname";
$key = "age";
$value = 23;
$redis->hset($name,$key,$value);
print_r($redis->hgetall($name));