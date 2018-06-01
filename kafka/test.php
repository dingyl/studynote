<?php
require_once 'KafkaQueue.php';

$host = '127.0.0.1:9092';
$name = 'test';

$queue = KafkaQueue::getInstance($host, $name);

while(1)
{
    $message = $queue->pop();

    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            var_dump($message);
            $queue->commit();
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            echo "No more messages; will wait for more\n";
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            echo "Timed out\n";
            break;
        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}