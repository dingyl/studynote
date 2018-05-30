<?php
$rk = new RdKafka\Producer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('localhost:9092');

$index = mt_rand(1, 4);
$theme = "test-" . $index;
$topic = $rk->newTopic($theme);
$topic->produce(RD_KAFKA_PARTITION_UA, 0, $theme . "Message " . time());
