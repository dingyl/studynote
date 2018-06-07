<?php
require_once './BenchUtil.php';
define('ROOT', __DIR__);

$models_dir = ROOT . DIRECTORY_SEPARATOR . 'models';

BenchUtil::autoLoad(ROOT);

BenchUtil::bench($models_dir);

