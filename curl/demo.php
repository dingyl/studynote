<?php
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

//curl请求接口
$ch = curl_init();
$url = "http://www.zixue.it/";
//设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);

//设置返回内容，或者直接将内容给浏览器
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//抓取URL并返回抓取内容
$content = curl_exec($ch);
$matchs = [];
//获取所有的a链接
//preg_match_all("/(<a[^>]*>)(.*?)(<\/a>)/",$content,$matchs);
preg_match_all("/<a[^>]*>.*?<\/a>/",$content,$matchs);
p($matchs);

// 关闭cURL资源，并且释放系统资源
curl_close($ch);