<?php
//include '../pdo/db.php';

$server = new swoole_websocket_server("0.0.0.0", 9501);

class Nodb{
	protected static $ins;
	protected $prefix = 'clis-';
	protected $host = '127.0.0.1';
	protected $port = 6379;
	protected $redis;
	protected function __construct(){

	}

	protected function __clone(){

	}

	public static function getIns(){
		if(!self::$ins instanceof self){
			self::$ins = new self();
		}
		return self::$ins;
	}

	public function init(){
		$this->redis = new Redis();
		$this->redis->connect($host,$port);
		return $this;
	}

	public function set($key,$value){
		return $this->redis->set($this->prefix.$key,$value);
	}

	public function get($key){
		return $this->redis->get($this->prefix.$key);
	}

	public function exists($key){
		return $this->redis->exists($this->prefix.$key);
	}

	public function keys($key){
		return $this->redis->keys($this->prefix.$key);
	}
}

$fd_userid_prefix = 'fd-id';
$userid_fd_prefix ='id-fd';
$redis = new Redis();
$redis->connect('127.0.0.1',6379);
$redis->flushdb();

//$psql = Db::getIns();

$server->on('open', function (swoole_websocket_server $server, $request) {
	global $redis,$fd_userid_prefix,$userid_fd_prefix,$psql;
	echo 11111;
	$redis->set($fd_userid_prefix.$request->fd,$request->fd);
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    global $redis,$fd_userid_prefix,$userid_fd_prefix,$psql;

    $msg = $frame->data;
    //绑定相应的用户信息

    foreach($redis->keys($fd_userid_prefix.'*') as $v){
    	$fd = intval(substr($v, strlen($fd_userid_prefix)));
    	$userid = $redis->get($v);
    	$server->push($fd,$msg);
    }
});

$server->on('close', function ($server, $fd) {
	global $redis,$fd_userid_prefix,$userid_fd_prefix,$psql;
    $redis->del($fd_userid_prefix.$fd);
});
$server->start();







