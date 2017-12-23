<?php
require 'CacheInterface.php';
require 'AbstractCache.php';
require 'RedisCache.php';
require 'SsdbCache.php';
$config = 'localhost:6379';
$redis = RedisCache::getReaderCache($config);
$ssdb = SsdbCache::getIns("localhost",'8888');

//$redis->set('username','ding');
echo $redis->get('username');