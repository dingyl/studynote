<?php
require_once 'KafkaQueue.php';

$host = '127.0.0.1:9092';
$name = 'test';

$queue = KafkaQueue::getInstance($host, $name);



while(1)
{
    $message = $queue->pop();
}