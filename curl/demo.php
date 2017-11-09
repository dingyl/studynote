<?php
include("../functions.php");
include("Curl.class.php");


$url = "http://edu.com/wx/products?new_register=1";
$curl = new Curl($url);
$agent = "Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN";
//模仿微信登录
$curl->setUserAgent($agent);
echo $curl->getContent();

?>








