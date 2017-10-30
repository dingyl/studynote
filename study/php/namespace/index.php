<?php
include "./demo.php";
include "./test.php";
$demo =new \demo\Factory();
$test =new \test\Factory();
$demo->test();
$test->test();