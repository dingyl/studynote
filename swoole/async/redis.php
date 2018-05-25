<?php

$client = new swoole_redis();
$client->connect('127.0.0.1', 6379, function (swoole_redis $client, $result) {
    echo "connect\n";
    var_dump($result);
    $client->set('test-key', 'swoole', function (swoole_redis $client, $result) {
        var_dump($result);
        $client->get('test-key', function (swoole_redis $client, $result) {
            var_dump($result);
            $client->close();
        });
    });
});