<?php
include __DIR__ . '/../vendor/autoload.php';
$queue = Pheanstalk\Pheanstalk::create('127.0.0.1');
while (true) {
    $job = $queue->reserve();
    echo $job->getData() . '-' . $job->getId() . PHP_EOL;
    $queue->delete($job);
}
