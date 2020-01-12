<?php
include __DIR__ . '/../vendor/autoload.php';
$queue = Pheanstalk\Pheanstalk::create('127.0.0.1');
$queue->put(uniqid(),10,10);
