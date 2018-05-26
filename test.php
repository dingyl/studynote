<?php

$directorys = new DirectoryIterator('/tmp');

foreach ($directorys as $directory) {
    echo $directory . PHP_EOL;
}