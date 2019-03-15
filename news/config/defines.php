<?php
# app信息
define('APP_ROOT', dirname(__DIR__));
define('APP_NAME', basename(APP_ROOT));

# 配置目录信息
define('CONTROLLER_DIR', APP_ROOT . '/controllers');
define('MODELS_DIR', APP_ROOT . '/models');
define('VIEWS_DIR', APP_ROOT . '/views');
define('LIBS_DIR', APP_ROOT . '/libs');
define('LOG_DIR', APP_ROOT . '/logs');
define('LOG_FILE', LOG_DIR . '/development.log');

# 数据库信息
define('DB_TYPE', 'mysql');
define('DB_NAME', 'news');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

# 错误编码
define('ERROR_CODE_SUCCESS', 0);
define('ERROR_CODE_FAIL', 1);