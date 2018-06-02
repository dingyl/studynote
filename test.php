<?php

$host = '127.0.0.1:9092';
$name = 'test';

$group_id = md5($name);

$conf = new \RdKafka\Conf();
$conf->set('group.id', $group_id);
$reader = new \RdKafka\Consumer($conf);
$reader->addBrokers($host);
$topicConf = new \RdKafka\TopicConf();
$topicConf->set('auto.commit.enable', 'false');
$topicConf->set('offset.store.method', 'file');
$topicConf->set('offset.store.path', sys_get_temp_dir());
//$topicConf->set('auto.offset.reset', 'smallest');
//$topicConf->set('auto.commit.interval.ms', 1000);
$topic = $reader->newTopic($name, $topicConf);
$topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);
while (1) {

    $message = $topic->consume(0, 60 * 1000);
    if ($message) {
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                var_dump($message);
//                $topic->offsetStore($message->partition, $message->offset);
//                $topic->consumeStop($message->partition);
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
}