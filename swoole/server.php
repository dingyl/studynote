<?php
include("../functions.php");

$http = new swoole_http_server('localhost', 9501);

$http->on('request', function ($request, $response) {
    //将所有请求信息数据返回给浏览器访问者
    //获取请求的数据 $request->get $request->post $request->cookie $request->server $request->file



    $hr = "\r\n";
    $content = "";
    $content.="print post$hr";
    $content.=var_export($request->post,true);
    $content.="print file$hr";
    $content.=var_export($request->files,true);
    $content.="print server$hr";
    $content.=var_export($request->server,true);
    $content.="print cookie$hr";
    $content.=var_export($request->cookie,true);
    put_log($content,"./swoole.log");
    echo "this is request$hr";
    //可以发现浏览器请求依次这里面的内容，执行了两次
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end($content);
});

$http->start();