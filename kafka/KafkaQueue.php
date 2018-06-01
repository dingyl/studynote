<?php

# kafka消息队列
class KafkaQueue
{
    protected $name;
    protected $id;
    protected $consumer_topic;
    protected $producer_topic;
    protected $message;
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
        $this->producer_topic = $writer->newTopic($name);


        # 消费者
        $conf = new \RdKafka\Conf();
        $conf->set('group.id', $this->id);
        $reader = new \RdKafka\Consumer($conf);
        $reader->addBrokers($host);
        $topicConf = new \RdKafka\TopicConf();
//        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('offset.store.method', 'file');
        $topicConf->set('offset.store.path', sys_get_temp_dir());
//        $topicConf->set('auto.offset.reset', 'smallest');
        $topicConf->set('auto.commit.enable', 'false');
        $this->consumer_topic = $reader->newTopic($name, $topicConf);
        $this->consumer_topic->consumeStart(self::DEFAULT_PARTITION, RD_KAFKA_OFFSET_STORED);
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

    # 发送消息
    public function push($message)
    {
        $this->producer_topic->produce(self::DEFAULT_PARTITION, 0, $message);
    }

    # 获取消息
    public function pop()
    {

        $message = $this->consumer_topic->consume(self::DEFAULT_PARTITION, 120 * 1000);
        $this->message = $message;
        return $message;
    }

    # 取指定位置的消息
    public function pos($offset)
    {
        $this->consumer_topic->consumeStart(self::DEFAULT_PARTITION, $offset);
    }

    public function commit()
    {
        if ($this->message && $this->message->err == RD_KAFKA_RESP_ERR_NO_ERROR) {
            $this->consumer_topic->offsetStore($this->message->partition, $this->message->offset);
        }
    }
}