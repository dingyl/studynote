<?php
$rk = new RdKafka\Consumer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('localhost:9092');
$offset = 1;
$topic = $rk->newTopic('test');
$partition = 0;
$topic->consumeStart($partition, 0);
while (true) {
    $message = $topic->consume($partition, 3);
    if (!is_object($message)) {
        continue;
    }
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            print_r($message);
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            echo 'eof' . PHP_EOL;
            sleep(1);
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            echo 'out' . PHP_EOL;
            sleep(1);
            break;
        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}