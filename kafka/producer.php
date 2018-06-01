<?php
//$rk = new RdKafka\Producer();
//$rk->setLogLevel(LOG_DEBUG);
//$rk->addBrokers('localhost:9092');
//$theme = "test";
//$topic = $rk->newTopic($theme);
//$topic->produce(RD_KAFKA_PARTITION_UA, 0, $theme . "Message " . time());
//


require_once "KafkaQueue.php";
$host = '127.0.0.1:9092';
$name = 'test';

$queue = KafkaQueue::getInstance($host, $name);

$queue->push('dingyaoli' . time());
