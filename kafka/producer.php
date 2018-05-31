<?php
$rk = new RdKafka\Producer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers('localhost:9092');
$theme = "test";
$topic = $rk->newTopic($theme);
$topic->produce(RD_KAFKA_PARTITION_UA, 0, $theme . "Message " . time());
