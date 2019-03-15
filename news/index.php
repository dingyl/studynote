<?php
require_once "config/defines.php";
require_once "config/utils.php";
require_once "libs/SimpleLoader.php";
$loader = new SimpleLoader();
$loader->autoLoader();
$loader->run();