<?php
# 所有的子进程挣抢一个队列中的内容


$workers = [];
$worker_num = 10;

for($i = 0; $i < $worker_num; $i++)
{
    $process = new swoole_process('callback_function', false, false);

    $process->useQueue();
    $pid = $process->start();
    $workers[$pid] = $process;
}

function callback_function(swoole_process $worker)
{
    $recv = $worker->pop();
    echo "From Master: $recv, worker_pid: {$worker->pid}\n";
    sleep(2);
    $worker->exit(0);
}

foreach($workers as $pid => $process)
{
    $process->push("hello worker[$pid]");
}

for($i = 0; $i < $worker_num; $i++)
{
    $ret = swoole_process::wait();
    $pid = $ret['pid'];
    unset($workers[$pid]);
    echo "Worker Exit, PID=".$pid.PHP_EOL;
}

