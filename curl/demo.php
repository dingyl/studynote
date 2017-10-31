<?php
include("../functions.php");
//curl获取网页内容  同时也可用来发送get请求
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

/*
$matchs = [];
$url = "http://www.zixue.it/";
p(curl_href($url));
*/


//发送post请求
function curl_post($url,$data){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_POST, 1);
    //添加文件上传功能
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); //  PHP 5.6.0 后必须设置
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


//上传文件 并传输数据
function curl_sendfile($url,$filepath,$time=30)
{
    //$filepath 文件的真实物理地址,upfile用来获取$_FILES['upfile']
    $post_data = array(
        'filename' => basename($filepath),
        'file'=>'@'.$filepath
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    //添加文件上传功能
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); //  PHP 5.6.0 后必须设置
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    //设置上传超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, $time);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

p($_FILES);
$url = "study/curl/upload.php";
echo curl_sendfile($url,$_FILES['img']['tmp_name']);

$file = "./demo.txt";
echo curl_sendfile($url,$file);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data" >
    <input type="file" name="img"/>
    <input type="submit">
</form>
</body>
</html>



