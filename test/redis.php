<?php

class BenchTest
{
    protected $start_time;
    protected $end_time;

    protected $info = '';

    public function start()
    {
        $this->start_time = microtime(true);
    }

    public function execute($name, $function)
    {
        $start = microtime(true);
        $str = $name . '任务' . date('Y-m-d H:i:s') . '时,开始执行' . PHP_EOL;
        $function();
        $end = microtime(true);
        $str .= $name . '任务耗时' . ($end - $start) . 's' . PHP_EOL;
        $this->info .= $str;
    }

    public function end()
    {
        $this->end_time = microtime(true);
        $this->info .= '测试总耗时' . ($this->end_time - $this->start_time) . 's' . PHP_EOL;
    }

    public function exception($str)
    {
        echo $str . PHP_EOL;
    }

    # 分析
    public function explain()
    {
        echo $this->info;
    }
}


$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$prefix = 'age_';
$num = 1000000;

$bench = new BenchUtil();
$bench->start();

//$bench->execute('set赋值测试', function () use ($redis, $prefix, $num) {
//    for ($i = 1; $i <= $num; $i++) {
//        $redis->set($prefix . $i, $i);
//    }
//});
//
//
//$bench->execute('set取值测试', function () use ($redis, $prefix, $num) {
//    for ($i = 1; $i <= $num; $i++) {
//        if ($redis->get($prefix . $i) != $i) {
//            echo $i . "异常" . PHP_EOL;
//        }
//    }
//});

$bench->execute('set随机取值测试', function () use ($redis, $prefix, $num) {
    $index = mt_rand(1, $num);
    if ($redis->get($prefix . $index) != $index) {
        echo $index . "异常" . PHP_EOL;
    }
});


$bench->end();

$bench->explain();
