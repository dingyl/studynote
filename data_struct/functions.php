<?php
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

function put_log($msg,$file='./put.log'){
    $msg = var_export($msg,true);
    $ip_addr = $_SERVER['REMOTE_ADDR'];
    $delimiter = "\r\n";
    $msg = $ip_addr.' '.date("Y-m-d H:i:s",time()).$delimiter.$msg.$delimiter;
    error_log($msg,3,$file);
}