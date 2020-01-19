<?php
include __DIR__ . '/../vendor/autoload.php';
$queue = Pheanstalk\Pheanstalk::create('127.0.0.1', 11300);
while (true) {
//    $job = $queue->watch('demo')->ignore('default')->reserveWithTimeout(10);
    $job = $queue->watch('demo')->ignore('default')->peekDelayed();
    if (isset($job)) {
        try {
            echo $job->getData() . '-' . $job->getId() . PHP_EOL;
//            $queue->bury($job);
        } catch (Exception $exception) {
            $queue->bury($job);
        }
    }
}
