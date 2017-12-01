<?php

$temp = ['match'=>['text'=>'ding']];
$json = '{"match":{"text":"ding"},"match":{"text":"hello"}}';
echo json_encode($temp);
print_r(json_decode($json,true));

var_export($temp);