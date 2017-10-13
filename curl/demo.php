<?php
//打印数组函数
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

//curl获取网页内容
function curl_content($url){
    //curl请求接口
    $ch = curl_init();
    //设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //设置返回内容，或者直接将内容给浏览器
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //抓取URL并返回抓取内容
    $temp = curl_exec($ch);
    // 关闭cURL资源，并且释放系统资源
    curl_close($ch);
    return $temp;
}

//curl获取所有链接
function curl_href($url){
    preg_match_all("/<a href=\"([^\'\"]*)\" [^>]*>(.*?)<\/a>/",curl_content($url),$matchs);
    $arr = [];
    foreach($matchs[1] as $k=>$href){
        //去除掉空连接
        if(substr($href,0,12)!="javascript:;"){
            //补全链接地址
            if(substr($href,0,4)!="http"){
                $href=$url.$href;
            }
            array_push($arr,$href);
        }
    }
    return $arr;
}

$matchs = [];
$url = "http://www.zixue.it/";
p(curl_href($url));


