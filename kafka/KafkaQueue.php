<?php

class KafkaQueue
{
    protected $name;
    protected $id;
    protected $consumer;
    protected $producer;
    protected static $ins;

    const DEFAULT_PARTITION = 0;

    protected function __construct($host, $name)
    {
        $this->name = $name;
        $this->id = md5($name);

        # 生产者
        $writer = new \RdKafka\Producer();
        $writer->setLogLevel(LOG_DEBUG);
        $writer->addBrokers($host);
        $this->producer = $writer->newTopic($name);


        # 消费者
        $conf = new \RdKafka\Conf();
        $conf->set('group.id', $this->id);
        $reader = new \RdKafka\Consumer($conf);
        $reader->addBrokers($host);
        $topicConf = new \RdKafka\TopicConf();
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('offset.store.method', 'file');
        $topicConf->set('offset.store.path', sys_get_temp_dir());
        $topicConf->set('auto.offset.reset', 'smallest');
        $topicConf->set('auto.commit.enable', 'false');
        $this->consumer = $reader->newTopic($name, $topicConf);
        $this->consumer->consumeStart(self::DEFAULT_PARTITION, RD_KAFKA_OFFSET_STORED);
    }

    protected function __clone()
    {
    }

    public static function getInstance($host, $name)
    {
        if (!self::$ins instanceof self) {
            self::$ins = new self($host, $name);
        }
        return self::$ins;
    }

    public function push($message)
    {
        $this->producer->produce(self::DEFAULT_PARTITION, 0, $message);
    }

    public function pop()
    {
        return $this->consumer->consume(self::DEFAULT_PARTITION, 120 * 1000);
    }

    public function commit($message)
    {
        $this->consumer->offsetStore($message->partition, $message->offset);
    }
}