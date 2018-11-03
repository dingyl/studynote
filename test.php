<?php

$arr = [1, 2, 3, 4];
$res = array_reduce($arr, function ($x, $y) {
    return $x . '-' . $y;
});


echo $res;