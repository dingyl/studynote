<?php
require 'DbSearch.php';
$hosts = ['localhost:9200'];
$client =  DbSearch::getIns($hosts,'order','testtable');

print_r($client->findAll());