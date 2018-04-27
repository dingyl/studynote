<?php

$rk = new RdKafka\Producer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('localhost:9092');
$topic = $rk->newTopic('test');
$topic->produce(0, 0, 'xxxxxxxxxx' . date('Y-m-d H:i:s', time()));

$rk = new RdKafka\Consumer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('172.16.2.95:9092');//brokerList ip 列表
$dsp = new posteventv2();
$topic = $rk->newTopic('classline_alarm_runline');//topic
$queue = $rk->newQueue();
for ($partition = 0; $partition < 4; $partition++) {//分区个数，这里服务端开了4个
//开始订阅数据
    $topic->consumeQueueStart($partition, RD_KAFKA_OFFSET_END, $queue); //RD_KAFKA_OFFSET_END 永远都从最新的开始取数据
}
while (true) {
    $message = $queue->consume(100);
    if (!is_object($message)) {
        continue;
    }
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            tools::datalog(var_export($message, true), 'classline_alarm_runline');//获取到的数据，订阅方根据自己逻辑自己处理
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