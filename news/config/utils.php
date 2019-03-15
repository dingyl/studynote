<?php
require_once 'defines.php';

function is_cli()
{
    return preg_match("/cli/i", php_sapi_name()) ? 1 : 0;
}

function info()
{
    $args = func_get_args();
    $content = argsToStr($args);
    $time = date('Y-m-d H:i:s');
    $backtrace = debug_backtrace();
    $backtrace_line = array_shift($backtrace);
    $backtrace_call = array_shift($backtrace);
    $file = substr($backtrace_line['file'], strlen(APP_ROOT));
    $line = $backtrace_line['line'];
    $func = $backtrace_call['function'];
    $info = "[INFO] $time [$file:$line $func] $content" . PHP_EOL;
    if (is_cli()) {
        echo $info;
    } else {
        error_log($info, 3, LOG_FILE);
    }
}

function argsToStr($args)
{
    $data = [];
    foreach ($args as $arg) {
        if (is_bool($arg)) {
            $data[] = var_export($arg, true);
            continue;
        }
        if (is_array($arg)) {
            $data[] = json_encode($arg, JSON_UNESCAPED_UNICODE);
            continue;
        }
        $data[] = $arg;
    }
    return implode(' ', $data);
}


function echoLine()
{
    $args = func_get_args();
    $content = argsToStr($args);
    echo $content . PHP_EOL;
}

# 下划线转驼峰方法
function underLineString2Camel($attribute)
{
    return str_replace(' ', '', ucwords(implode(' ', explode('_', $attribute))));
}

# 驼峰转下划线方法
function camel2UnderLineString($camel)
{
    return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $camel));
}

# 生成随机字符串
function randStr($len)
{
    $str = '1234567890abcdefghijklmnopqrstuvwxyz';
    $str_len = strlen($str);
    $rand_str = ''; # 用来存放生成的随机字符串
    for ($i = 0; $i < $len; $i++) {
        $rand_code = mt_rand(0, $str_len - 1);
        $rand_str .= $str[$rand_code];
    }
    return $rand_str;
}

# 获取数组指定key的值
function fetch($array, $key, $default_value = '')
{
    return isset($array[$key]) ? $array[$key] : $default_value;
}

function isBlank($value)
{
    return (is_string($value) && trim($value) === '') || $value === false || is_null($value) || $value === 0 || (is_array($value) && count($value) == 0);
}


function httpGet($url)
{
    $info = parse_url($url);
//    print_r($info);
    $port = isset($info['port']) ? $info['port'] : 80;
    $fp = fsockopen($info["host"], $port);
    if (!$fp) {
        return '';
    }
    $head = "GET " . $info['path'] . " HTTP/1.1\r\n";
    $head .= "Host: " . $info['host'] . "\r\n";
//    $head .= "Accept-Encoding: gzip, deflate\r\n\r\n";
    $head .= "Connection: Close\r\n\r\n";
    fwrite($fp, $head);
    $line = '';
    while (!feof($fp)) {
        $line .= fread($fp, 2048);
    }
    fclose($fp);
    return $line;
}