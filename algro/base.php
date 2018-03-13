<?php
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

function swap(&$a,&$b){
    $a ^= $b;
    $b ^= $a;
    $a ^= $b;
}