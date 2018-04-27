<?php
$rk = new RdKafka\Consumer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('localhost:9092,localhost:9192,localhost:9292');
$offset = 1;
$topic = $rk->newTopic('test-x');
$partition = 0;
$topic->consumeStart($partition, $offset);
while (true) {
    $message = $topic->consume($partition, 100);
    if (!is_object($message)) {
        continue;
    }
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            print_r($message);
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            break;
        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}
