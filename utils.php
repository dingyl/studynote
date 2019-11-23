<?php

/**
 * 功    能: argsToStr 将参数转换成字符串方便打印输出
 * 修改日期: 2019-05-11
 *
 * @param array $args 参数
 * @return string
 */
function argsToStr($args)
{
    $data = [];
    foreach ($args as $arg) {
        if (is_bool($arg)) {
            $data[] = var_export($arg, true);
            continue;
        }
        if (is_object($arg)) {
            $data[] = get_class($arg) . '@' . $arg;
            continue;
        }
        if (is_array($arg)) {
            # models数组处理
            $data[] = json_encode($arg, JSON_UNESCAPED_UNICODE);
        }
        $data[] = $arg;
    }
    $content = implode(' ', $data);
    return $content;
}


############################# 日志组函数 #############################
function logInfo()
{
    $argv = func_get_args();
    $content = argsToStr($argv);
    $time = date('Y-m-d H:i:s');
    $backtrace = debug_backtrace();
    $backtrace_line = array_shift($backtrace);
    $backtrace_call = array_shift($backtrace);
    $file = substr($backtrace_line['file'], strlen(ROOT_PATH));
    $line = $backtrace_line['line'];
    $func = $backtrace_call['function'];
    $message = "[ app-info ] $time [{$file}:{$line} {$func}] {$content}";
    if (isCli()) {
        echoLine($message);
    } else {
        $message = "[ app-info ] {$time} [{$file}:{$line} {$func}] {$content}";
    }
}

############################# 日志组函数 #############################

/**
 * 功    能: echoLine 命令行打印函数
 * 修改日期: 2019-05-11
 *
 * @return void
 */
function echoLine()
{
    $args = func_get_args();
    $content = argsToStr($args);
    echo $content . PHP_EOL;
}

/**
 * 功    能: daemon 开启守护进程模式
 * 修改日期: 2019-05-11
 *
 * @return void
 */
function daemon()
{
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("fork(1) failed!" . PHP_EOL);
    } else if ($pid > 0) {
        exit(0);
    }
    posix_setsid();
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("fork(2) failed!" . PHP_EOL);
    } else if ($pid > 0) {
        exit(0);
    }
}

/**
 * 功    能: isCli 判断是不是命令行请求
 * 修改日期: 2019-05-11
 *
 * @return bool
 */
function isCli()
{
    return PHP_SAPI === 'cli';
}


/**
 * 功    能: isEmail 判断是否是邮箱
 * 修改日期: 2019-05-11
 *
 * @param $email
 * @return bool
 */
function isEmail($email)
{
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * 功    能: isIdCard 验证身份证
 * 修改日期: 2019-05-11
 *
 * @param $id_card
 * @return bool
 */
function isIdCard($id_card)
{
    $id_card = strtoupper($id_card);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = [];
    if (!preg_match($regx, $id_card)) {
        return false;
    }
    if (15 == strlen($id_card)) {
        # 检查15位
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
        @preg_match($regx, $id_card, $arr_split);
        # 检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return false;
        } else {
            return true;
        }
    } else {
        # 检查18位
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id_card, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            # 检查生日日期是否正确
            return false;
        } else {
            # 检验18位身份证的校验码是否正确。
            # 校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $arr_ch = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            $sign = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int)$id_card{$i};
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id_card, 17, 1)) {
                return false;
            } else {
                return true;
            }
        }
    }
}

/**
 * 功    能: isMobile 判断是否是手机号
 * 修改日期: 2019-05-11
 *
 * @param $mobile
 * @return false|int
 */
function isMobile($mobile)
{
    return preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/', $mobile);
}

/**
 * 功    能: isBankCard 判断是否是校验银行卡
 * 修改日期: 2019-05-11
 *
 * @param string $card 银行卡号
 * @return bool
 */
function isBankCard($card)
{
    $len = strlen($card);
    $flag = is_numeric($card) && $len >= 8 && $len <= 28;
    if ($flag) {
        $arr_no = str_split($card);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $total *= 9;
        return $last_n == ($total % 10);
    }
    return false;
}

/**
 * 功    能: underLineString2Camel 下划线转驼峰方法
 * 修改日期: 2019-05-11
 *
 * @param $attribute
 * @return mixed
 */
function underLineString2Camel($attribute)
{
    return lcfirst(str_replace(' ', '', ucwords(implode(' ', explode('_', $attribute)))));
}

/**
 * 功    能: camel2UnderLineString 驼峰转下划线方法
 * 修改日期: 2019-05-11
 *
 * @param $camel
 * @return string
 */
function camel2UnderLineString($camel)
{
    return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $camel));
}


/**
 * 功    能: randStr 生成随机字符串
 * 修改日期: 2019-05-11
 *
 * @param $len
 * @return string
 */
function randStr($len)
{
    $str = '1234567890abcdefghijklmnopqrstuvwxyz';
    $str_len = strlen($str);
    $rand_str = '';
    for ($i = 0; $i < $len; $i++) {
        $rand_code = mt_rand(0, $str_len - 1);
        $rand_str .= $str[$rand_code];
    }
    return $rand_str;
}

/**
 * @return mixed
 */
/**
 * 获取客户端IP
 */
function clientIp()
{
    $ip = 'unknown';
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (strpos($ip, ',') !== false) {
        $ip = reset(explode(',', $ip));
    }
    return $ip;
}

/**
 * 功    能: fetch 获取数组指定key的值
 * 修改日期: 2019-05-11
 *
 * @param array $array 数组
 * @param string $key key
 * @param string $default_value 默认值
 * @param string $format 格式
 * @return string
 */
function fetch($array, $key, $default_value = '', $format = '%s')
{
    $default_type = gettype($default_value);
    if (isset($array[$key])) {
        $array[$key] = sprintf($format, $array[$key]);
        settype($array[$key], $default_type);
    }
    return isset($array[$key]) ? $array[$key] : $default_value;
}

/**
 * 功    能: beginOfDay 当天的开始时间
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function beginOfDay($time = null)
{
    $time = $time ?: time();
    return strtotime(date('Y-m-d', $time));
}

/**
 * 功    能: endOfDay 当天的结束时间
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function endOfDay($time = null)
{
    $time = $time ?: time();
    return strtotime(date('Y-m-d 23:59:59', $time));
}

/**
 * 功    能: beginOfMonth
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function beginOfMonth($time = null)
{
    $time = $time ?: time();
    return strtotime(date('Y-m-01', $time));
}

/**
 * 功    能: endOfMonth
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function endOfMonth($time = null)
{
    $time = $time ?: time();
    $first_day = date('Y-m-01', $time);
    return strtotime("$first_day +1 month -1 second");
}

/**
 * 功    能: beginOfHour
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function beginOfHour($time = null)
{
    $time = $time ?: time();
    return strtotime(date('Y-m-d H:00:00', $time));
}

/**
 * 功    能: endOfHour
 * 修改日期: 2019-05-11
 *
 * @param integer $time 时间戳
 * @return false|int
 */
function endOfHour($time = null)
{
    $time = $time ?: time();
    return strtotime(date('Y-m-d H:59:59', $time));
}


/**
 * 功    能: isBlank 判断是否为空
 * 修改日期: 2019-05-11
 *
 * @param mixed $object 参数
 * @return bool
 */
function isBlank($object)
{
    if (is_null($object) || '' === $object || (is_array($object) && count($object) < 1)) {
        return true;
    }
    return empty($object);
}

/**
 * 功    能: isPresent 判断是否非空
 * 修改日期: 2019-05-11
 *
 * @param mixed $object 参数
 * @return bool
 */
function isPresent($object)
{
    return !isBlank($object);
}

/**
 * 功    能: httpScheme 请求协议头
 * 修改日期: 2019-05-11
 *
 * @return string
 */
function httpScheme()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
    return $http_type;
}

/**
 * 根据当前服务器的协议自动转换请求协议
 * @param $url
 * @return string
 */
function convertUrl($url)
{
    $http_type = httpScheme();
    if ($pos = strpos($url, "//")) {
        $url = $http_type . ':' . substr($url, $pos);
    } else {
        $url = $http_type . '://' . $url;
    }
    return $url;
}

/**
 * @param $url
 * @param array $params
 * @return false|string
 */
function httpGet($url, $params = [])
{
    $url = convertUrl($url);
    $url .= strpos($url, '?') ? http_build_query($params) : '?' . http_build_query($params);
    $result = file_get_contents($url);
    return $result;
}

/**
 * @param $url
 * @param array $params
 * @param array $headers
 * @param array $files
 * @param int $timeout
 * @return bool|string
 */
function httpPost($url, $params = [], $headers = [], $files = [], $timeout = 30)
{
    $url = convertUrl($url);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    isPresent($headers) && curl_setopt($curl, CURLOPT_HEADER, $headers);
    $flag = version_compare(phpversion(), "5.5");
    $flag || curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
    $file_params = [];
    foreach ($files as $field => $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $file_params[$field . "[$k]"] = $flag ? new CURLFile($v) : '@' . $v;
            }
        } else {
            $file_params[$field] = $flag ? new CURLFile($value) : '@' . $value;
        }
    }
    $params = array_merge($params, $file_params);
    isPresent($params) && curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($curl);
    return $result;
}

/**
 * @param $url
 * @param $destination
 * @return bool
 */
function httpSave($url, $destination)
{
    $content = file_get_contents($url);
    $try_num = 3;
    while ($try_num--) {
        if ($content === false) {
            $content = file_get_contents($url);
        } else {
            break;
        }
    }
    if ($content && file_put_contents($destination, $content) !== false) {
        return $destination;
    } else {
        return false;
    }
}