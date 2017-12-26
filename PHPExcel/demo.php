<?php
require 'Excel.php';

$data = [
    ['name'=>'中国','age'=>23],
    ['name'=>'美丽人生','age'=>23],
    ['name'=>3,'age'=>23],
    ['name'=>4,'age'=>23],
];


//$path = Excel::write($data,'./');
Excel::export($data);


//$msg = Excel::read($path);
//print_r($msg);