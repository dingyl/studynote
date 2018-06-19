<?php

$objs = [];

for ($i = 1; $i <= 20; $i++) {
    $obj = new stdClass();
    $obj->site = mt_rand(1, 5);
    $objs[] = $obj;
}


$filters = array_map(function($obj){
    return $obj->site;
},$objs);


print_r($filters);