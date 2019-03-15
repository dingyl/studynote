<?php
require_once "../config/defines.php";
require_once "../config/utils.php";
require_once "../libs/SimpleLoader.php";
$loader = new SimpleLoader();
$loader->autoLoader();
$url = 'http://roll.news.sina.com.cn/news/gnxw/zs-pl/index_1.shtml';
\models\News::import($url);