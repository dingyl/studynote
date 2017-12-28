#!/usr/local/bin/php
<?php
$server = new swoole_websocket_server("0.0.0.0", 9501);
$fd_userid_prefix = 'fd-id';
$userid_fd_prefix ='id-fd';
$redis = new Redis();
$redis->connect('127.0.0.1',6379);
$redis->flushdb();
$server->on('open', function (swoole_websocket_server $server, $request) {
	global $redis,$fd_userid_prefix;
	$redis->set($fd_userid_prefix.$request->fd,$request->fd);
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    global $redis,$fd_userid_prefix;

    $msg = $frame->data;
    //绑定相应的用户信息
    foreach($redis->keys($fd_userid_prefix.'*') as $v){
    	$fd = intval(substr($v, strlen($fd_userid_prefix)));
    	$userid = $redis->get($v);
    	$server->push($fd,$msg);
    }
});

$server->on('close', function ($server, $fd) {
	global $redis,$fd_userid_prefix;
    $redis->del($fd_userid_prefix.$fd);
});
$server->start();







