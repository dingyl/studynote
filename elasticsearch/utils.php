<?php
//打印辅助函数
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

//日志函数
function debug()
{
    $args = func_get_args();
    $content = argsToStr($args);
    $time = date('Y-m-d H:i:s');
    $backtrace = debug_backtrace();
    $backtrace_line = array_shift($backtrace);
    $backtrace_call = array_shift($backtrace);
    $file = substr($backtrace_line['file'], strlen($_SERVER['DOCUMENT_ROOT']));
    $line = $backtrace_line['line'];
    $func = $backtrace_call['function'];
    $info = "[DEBUG]$time [$file:$line $func]$content" . PHP_EOL;
    if (isCli() || defined('TASK_SERVER')) {
        echo $info;
    } else {
        error_log($info, 3, DEBUG_LOG_FILE);
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
        if (is_object($arg)) {
            if ($arg instanceof \yii\db\ActiveRecord) {
                $data[] = get_class($arg) . '@' . json_encode($arg->toArray(), JSON_UNESCAPED_UNICODE);
            } else {
                $data[] = var_export($arg, true);
            }
            continue;
        }
        if (is_array($arg)) {
            $str = [];
            //models数组处理
            if ($arg[0] instanceof \yii\db\ActiveRecord) {
                foreach ($arg as $model) {
                    $str[] = json_encode($model->toArray(), JSON_UNESCAPED_UNICODE);
                }
                $data[] = get_class($arg[0]) . '@[' . implode(',', $str) . ']';
            } else {
                $data[] = json_encode($arg, JSON_UNESCAPED_UNICODE);
            }
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

function httpType()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http_type;
}

//GBK页面可改为gb2312，其他随意填写为UTF8
function pinyin($_String, $_Code = 'UTF8')
{
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
        "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);
    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++) {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160) {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data)
{
    if ($_Num > 0 && $_Num < 160) {
        return chr($_Num);
    } elseif ($_Num < -20319 || $_Num > -10247) {
        return '';
    } else {
        foreach ($_Data as $k => $v) {
            if ($v <= $_Num) break;
        }
        return $k;
    }
}

function _U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80) {
        $_String .= $_C;
    } elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}


function server($field = '')
{
    if ($field) {
        return $_SERVER[$field];
    }
    return $_SERVER;
}


/**
 * 获取客户端浏览器
 * @return string
 */
function getBrowser()
{
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return $br;
    } else {
        return "获取浏览器信息失败！";
    }
}

/**
 * 判断是否是微信浏览器
 * @return bool
 */
function isWeixin()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 获取客户端语言
 * @return bool|string
 */
function getLang()
{
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $lang = substr($lang, 0, 5);
        if (preg_match("/zh-cn/i", $lang)) {
            $lang = "简体中文";
        } elseif (preg_match("/zh/i", $lang)) {
            $lang = "繁体中文";
        } else {
            $lang = "English";
        }
        return $lang;

    } else {
        return "获取浏览器语言失败！";
    }
}


/**
 * 获取客户端操作系统
 * @return string
 */
function getOs()
{
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $OS = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/win/i', $OS)) {
            $OS = 'Windows';
        } elseif (preg_match('/mac/i', $OS)) {
            $OS = 'MAC';
        } elseif (preg_match('/linux/i', $OS)) {
            $OS = 'Linux';
        } elseif (preg_match('/unix/i', $OS)) {
            $OS = 'Unix';
        } elseif (preg_match('/bsd/i', $OS)) {
            $OS = 'BSD';
        } else {
            $OS = 'Other';
        }
        return $OS;
    } else {
        return false;
    }
}

/**
 * 获取本地真实ip
 * @return mixed|string
 */
function getLocalIp()
{

}

/**
 * 获取访客真实ip
 * @return mixed
 */
function getClientIp()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //获取代理ip
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    if ($ip) {
        $ips = array_unshift($ips, $ip);
    }
    $count = count($ips);
    for ($i = 0; $i < $count; $i++) {
        if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {//排除局域网ip
            $ip = $ips[$i];
            break;
        }
    }
    $tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
    return $tip;
}


/**
 * 获取ip的地址信息
 * @param string $ip
 * @return string
 */
function getAddress($ip)
{
    $info = file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=$ip");
    $info = json_decode($info, true);
    return $info['data'];
}


/**
 * 根据ip地址获取城市信息
 * @return array
 */
function getLocalAddress()
{
    $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
    $json = file_get_contents($url);
    $address = json_decode($json);
    $info = [];
    if (isset($address->country)) {
        $info = [
            'country' => $address->country,
            'province' => $address->province,
            'province_pinyin' => pinyin($address->province),
            'city' => $address->city,
            'city_pinyin' => pinyin($address->city)
        ];
    }
    return $info;
}


/**
 * 根据城市名获取天气信息
 * @param $city_code
 * @return bool|mixed|string
 */
function getWeatherInfo($city_code)
{
    $url = "http://wthrcdn.etouch.cn/weather_mini?citykey=$city_code";
    $info = file_get_contents($url);
    $info = gzdecode($info);
    $info = json_decode($info, true);
    return $info;
}


/**
 * 手机归属地运营商信息查询
 * @param $mobile
 * @return array
 */
function getMobileInfo($mobile)
{
    $url = "https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=$mobile";
    $info = file_get_contents($url);
    $info = trim(explode('=', $info)[1]);
    $info = preg_replace('/[\t\r\n \{\}]+/', '', $info);
    $info = explode(',', $info);
    $temp = [];
    foreach ($info as $value) {
        $key_value = explode(':', $value);
        $temp[$key_value[0]] = trim(iconv('GB2312', 'UTF-8', $key_value[1]), '\'');
    }
    return $temp;
}

/**
 * 下划线转驼峰方法
 * @param $attribute
 * @return mixed
 */
function underLineString2Camel($attribute)
{
    return str_replace(' ', '', ucwords(implode(' ', explode('_', $attribute))));
}

/**
 * 驼峰转下划线方法
 * @param $camel
 * @return string
 */
function camel2UnderLineString($camel)
{
    return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $camel));
}

//判断日期格式是否合法
function checkData($date, $format = "Y-m-d H:i:s")
{
    $time = strtotime($date);
    return date($format, $time) == $date;
}

/**
 * 判断是不是命令行请求
 * @return bool
 */
function isCli()
{
    return isset($_SERVER['SHELL']);
}


function renderJSON($error_code, $error_reason, $params = [])
{
    $return_data = [
        'error_code' => $error_code,
        'error_reason' => $error_reason,
    ];
    $return_data = array_merge($return_data, $params);
    header('Content-type: text/json');
    echo json_encode($return_data, JSON_UNESCAPED_UNICODE);
    die;
}

//根据变量名称,获取常量值
function d($name)
{
    return defined($name) ? constant($name) : '';
}

//生成随机字符串
function randStr($len)
{
    $str = '1234567890abcdefghijklmnopqrstuvwxyz';
    $str_len = strlen($str);
    $rand_str = ''; //用来存放生成的随机字符串
    for ($i = 0; $i < $len; $i++) {
        $rand_code = mt_rand(0, $str_len - 1);
        $rand_str .= $str[$rand_code];
    }
    return $rand_str;
}

//获取数组指定key的值
function fetch($array, $key, $default_value = '')
{
    return isset($array[$key]) ? $array[$key] : $default_value;
}

function httpGet($url, $body = [], $headers = [], $mime = null)
{
    $url .= strpos($url, '?') ? http_build_query($body) : '?' . http_build_query($body);
    $request = \Httpful\Request::get($url, $mime)->addHeaders($headers);
    $request->timeout(30);
    $resp = $request->withoutAutoParsing()->send();
    return $resp->body;
}

function httpPost($url, $body = [], $headers = [], $files = [], $mime = null)
{
    if (!isset($headers['User-Agent'])) {
        $headers['User-Agent'] = 'Mozilla/5.0';
    }
    $request = \Httpful\Request::post($url)->body($body, $mime)->addHeaders($headers);
    $request->timeout(30);
    if ($files) {
        $request = $request->attach($files);
    } else {
        if (!isset($headers['Content-Type'])) {
            $request = $request->sendsType(\Httpful\Mime::FORM);
        }
    }
    $resp = $request->withoutAutoParsing()->send();
    return $resp->body;
}

function getMimes()
{
    $res = [
        'html' => 'text/html',
        'htm' => 'text/html',
        'shtml' => 'text/html',
        'css' => 'text/css',
        'xml' => 'text/xml',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'atom' => 'application/atom+xml',
        'rss' => 'application/rss+xml',
        'mml' => 'text/mathml',
        'txt' => 'text/plain',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'wml' => 'text/vnd.wap.wml',
        'htc' => 'text/x-component',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'wbmp' => 'image/vnd.wap.wbmp',
        'ico' => 'image/x-icon',
        'jng' => 'image/x-jng',
        'bmp' => 'image/x-ms-bmp',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'webp' => 'image/webp',
        'jar' => 'application/java-archive',
        'war' => 'application/java-archive',
        'ear' => 'application/java-archive',
        'hqx' => 'application/mac-binhex40',
        'doc' => 'application/msword',
        'pdf' => 'application/pdf',
        'ps' => 'application/postscript',
        'eps' => 'application/postscript',
        'ai' => 'application/postscript',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'wmlc' => 'application/vnd.wap.wmlc',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        '7z' => 'application/x-7z-compressed',
        'cco' => 'application/x-cocoa',
        'jardiff' => 'application/x-java-archive-diff',
        'jnlp' => 'application/x-java-jnlp-file',
        'run' => 'application/x-makeself',
        'pl' => 'application/x-perl',
        'pm' => 'application/x-perl',
        'prc' => 'application/x-pilot',
        'pdb' => 'application/x-pilot',
        'rar' => 'application/x-rar-compressed',
        'rpm' => 'application/x-redhat-package-manager',
        'sea' => 'application/x-sea',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'tcl' => 'application/x-tcl',
        'tk' => 'application/x-tcl',
        'der' => 'application/x-x509-ca-cert',
        'pem' => 'application/x-x509-ca-cert',
        'crt' => 'application/x-x509-ca-cert',
        'xpi' => 'application/x-xpinstall',
        'xhtml' => 'application/xhtml+xml',
        'zip' => 'application/zip',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/x-m4a',
        'ra' => 'audio/x-realaudio',
        '3gpp' => 'video/3gpp',
        '3gp' => 'video/3gpp',
        'mp4' => 'video/mp4',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'webm' => 'video/webm',
        'flv' => 'video/x-flv',
        'm4v' => 'video/x-m4v',
        'mng' => 'video/x-mng',
        'asx' => 'video/x-ms-asf',
        'asf' => 'video/x-ms-asf',
        'wmv' => 'video/x-ms-wmv',
        'avi' => 'video/x-msvideo'
    ];
    return $res;
}


//数组转换为对象
function arrayToHash($array)
{
    $hash = new stdClass();
    foreach ($array as $k => $v) {
        $hash->$k = $v;
    }
    return $hash;
}

//解析文件获取文件类型
function getFileRealExtension($file)
{
    $default = 'undifined';
    if (is_file($file)) {
        $mimes = getMimes();
        $finfo = finfo_open(FILEINFO_MIME);
        $mime_type = finfo_file($finfo, $file);
        $ext = array_search($mime_type, $mimes);
        return $ext ? $ext : $default;
    }
    return $default;
}

//根据文件名后缀
function getFileExtension($file)
{
    $default = 'undifined';
    return is_file($file) ? pathinfo($file, PATHINFO_EXTENSION) : $default;
}


//验证身份证
function validateIdCard($id_card)
{
    $id_card = strtoupper($id_card);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id_card)) {
        return FALSE;
    }
    if (15 == strlen($id_card)) //检查15位
    {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

        @preg_match($regx, $id_card, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else      //检查18位
    {
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id_card, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];

        if (!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return false;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
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

function validateMobile($mobile)
{
    return preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/', $mobile);
}

function validateTelphone($telphone)
{
    return preg_match('/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{8}$/', $telphone);
}

function validateEmail($email)
{
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email);
}

function uuid()
{
    return md5(posix_getpid() . uniqid(mt_rand(), true));
}

//日期函数组开始
function beginOfDay($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    return strtotime(date('Y-m-d', $time));
}

function endOfDay($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    return strtotime(date('Y-m-d 23:59:59', $time));
}

function beginOfMonth($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    return strtotime(date('Y-m-01', $time));
}

function endOfMonth($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    $first_day = date('Y-m-01', $time);
    return strtotime("$first_day +1 month -1 second");
}

function beginOfHour($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d H:00:00', $time));
}

function endOfHour($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d H:59:59', $time));
}

//日期函数组结束


function dayOfWeek($time)
{
    $w = date('w', $time);
    $week = [
        '1' => '一',
        '2' => '二',
        '3' => '三',
        '4' => '四',
        '5' => '五',
        '6' => '六',
        '0' => '日',
    ];
    return '星期' . $week[$w];
}


//打印json输出
function printJson($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


//将 field = name[hello][world] 装换成数组
function getFieldArr($field)
{
    $pos = strpos($field, '[');
    $son_fields = [];
    if ($pos) {
        $son_fields[] = substr($field, 0, $pos);
        $field = substr($field, $pos);
        preg_match_all('/\[(.*?)\]/', $field, $matchs);
        $son_fields = array_merge($son_fields, $matchs[1]);
    } else {
        $son_fields[] = $field;
    }
    return $son_fields;
}

//将数组装换成 name[hello][world] 字符串
function getFieldStr($son_fields)
{
    $domain = array_shift($son_fields);
    if ($son_fields) {
        return $domain . '[' . implode('][', $son_fields) . ']';
    } else {
        return $domain;
    }
}


//绑定数据
function arrSetValue(&$arr, $field, $value)
{
    $son_fields = getFieldArr($field);
    setValue($arr, $son_fields, $value);
}

//递归绑定数据
function setValue(&$arr, &$son_fields, $value)
{
    $field = array_shift($son_fields);
    if ($son_fields) {
        setValue($arr[$field], $son_fields, $value);
    } else {
        $arr[$field] = $value;
    }
}

//数组获取值
function arrGetValue($arr, $field)
{
    $son_fields = getFieldArr($field);
    return getValue($arr, $son_fields);
}

//递归获取值
function getValue($arr, &$son_fields)
{
    $field = array_shift($son_fields);
    if ($son_fields) {
        return isset($arr[$field]) ? getValue($arr[$field], $son_fields) : null;
    } else {
        return isset($arr[$field]) ? $arr[$field] : null;
    }
}

function isEmail($email)
{
    if (isPresent($email)) {
        $exp = "/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i";
        return preg_match($exp, $email) ? true : false;
    } else {
        return false;
    }
}


function isInteger($integer)
{
    if ($integer) {
        return preg_match('/^\d+$/', $integer) || preg_match('/^-\d+$/', $integer);
    } else {
        return false;
    }
}

function isFloat($float)
{
    if ($float) {
        return isInteger($float) || preg_match('/^\d+\.\d+$/', $float) || preg_match('/^-\d+\.\d+$/', $float);
    } else {
        return false;
    }
}

//数组深度合并  两个数组格式必须统一，同一字段类型相同
function arrDeepMerge(&$arr1, &$arr2)
{
    foreach ($arr2 as $k => $v) {
        if (isset($arr1[$k]) && is_array($v)) {
            arrDeepMerge($arr1[$k], $v);
        } else {
            $arr1[$k] = $v;
        }
    }
}

//为接口开发使用
function getFiles($name)
{
    $files = [];
    $son_fields = getFieldArr($name);
    $domain = array_shift($son_fields);
    if ($son_fields) {
        $temp_name_fields = $son_fields;
        $error_fields = $son_fields;
        $size_fields = $son_fields;
        array_unshift($temp_name_fields, $domain, 'tmp_name');
        array_unshift($error_fields, $domain, 'error');
        array_unshift($size_fields, $domain, 'size');
        $temp_names = arrGetValue($_FILES, getFieldStr($temp_name_fields));
        $errors = arrGetValue($_FILES, getFieldStr($error_fields));
        $sizes = arrGetValue($_FILES, getFieldStr($size_fields));
    } else {
        $temp_name_fields = $son_fields;
        $error_fields = $son_fields;
        $size_fields = $son_fields;
        array_push($temp_name_fields, $domain, 'tmp_name');
        array_push($error_fields, $domain, 'error');
        array_push($size_fields, $domain, 'size');
        $temp_names = arrGetValue($_FILES, getFieldStr($temp_name_fields));
        $errors = arrGetValue($_FILES, getFieldStr($error_fields));
        $sizes = arrGetValue($_FILES, getFieldStr($size_fields));
    }
    if ($errors && $temp_names && $sizes) {
        foreach ($errors as $index => $error) {
            if ($error == ERROR_CODE_SUCCESS && $temp_names[$index] && $sizes[$index] > 0) {
                $files[] = $temp_names[$index];
            }
        }
    }
    return $files;
}

//$name = individual_qualification[identification_front_image_id]
//$name = image
function getFile($name)
{
    $son_fields = getFieldArr($name);
    $domain = array_shift($son_fields);
    if ($son_fields) {
        $temp_name_fields = $son_fields;
        $error_fields = $son_fields;
        $size_fields = $son_fields;
        array_unshift($temp_name_fields, $domain, 'tmp_name');
        array_unshift($error_fields, $domain, 'error');
        array_unshift($size_fields, $domain, 'size');
        $temp_name = arrGetValue($_FILES, getFieldStr($temp_name_fields));
        $error = arrGetValue($_FILES, getFieldStr($error_fields));
        $size = arrGetValue($_FILES, getFieldStr($size_fields));
    } else {
        $temp_name_fields = $son_fields;
        $error_fields = $son_fields;
        $size_fields = $son_fields;
        array_push($temp_name_fields, $domain, 'tmp_name');
        array_push($error_fields, $domain, 'error');
        array_push($size_fields, $domain, 'size');
        $temp_name = arrGetValue($_FILES, getFieldStr($temp_name_fields));
        $error = arrGetValue($_FILES, getFieldStr($error_fields));
        $size = arrGetValue($_FILES, getFieldStr($size_fields));
    }
    if ($temp_name && 0 == $error && 0 < $size) {
        return $temp_name;
    }
    return null;
}

function rectSort($arrays, $order, $sort_mode = SORT_REGULAR)
{
    $order_info = explode(' ',trim($order)) ;
    $field = $order_info[0];
    $sort_type = isset($order_info[1]) ? $order_info[1] : 'asc';
    $sort_type = strtolower($sort_type);
    if ($sort_type == 'asc') {
        $sort_type = SORT_ASC;
    } else {
        $sort_type = SORT_DESC;
    }
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$field];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_type, $sort_mode, $arrays);
    return $arrays;
}
