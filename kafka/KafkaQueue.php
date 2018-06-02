<?php

# kafka消息队列
class KafkaQueue
{
    protected $name;
    protected $id;
    protected $consumer_topic;
    protected $producer_topic;
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
        $topicConf->set('auto.commit.enable', 'false');
        $topicConf->set('offset.store.method', 'file');
        $topicConf->set('offset.store.path', sys_get_temp_dir());
        $topicConf->set('auto.commit.interval.ms', 100);
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

    # 发送字符消息
    public function push($message)
    {
        $this->producer_topic->produce(self::DEFAULT_PARTITION, 0, $message);
    }

    # 获取字符消息
    public function pop()
    {
        $message = $this->consumer_topic->consume(self::DEFAULT_PARTITION, 120 * 1000);
        if ($message && $message->err == RD_KAFKA_RESP_ERR_NO_ERROR) {
            return $message->payload;
        } else {
            return null;
        }
    }

    # 发送数组消息
    public function pushJson($data)
    {
        $message = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->push($message);
    }

    # 获取数组消息
    public function popJson()
    {
        $msg = $this->pop();
        if ($msg) {
            return json_decode($msg, true);
        } else {
            return null;
        }
    }
}