<?php

//日志文件地址
define('LOG_DIR',__DIR__);
define('LOG_FILE',LOG_DIR.'/development.log');

//定义系统换行符
//windows系统
if(in_array(PHP_OS,['WIN32', 'WINNT', 'Windows'])){
    define('SYSTEM_CRLF',"\r\n");
}else if(in_array(PHP_OS,['Darwin'])){//mac系统
    define('SYSTEM_CRLF',"\r");
}else{
    define('SYSTEM_CRLF',"\n");
}

function getMimes()
{
    return [
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
        'aac' => 'audio/x-hx-aac-adts',
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
}


/**
 * 打印辅助函数
 * @param $arr
 */
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

/**
 * 切换日志目录
 * @param $file_name
 */
function switchLog($file_name){
    define('LOG_FILE',LOG_DIR.DIRECTORY_SEPARATOR.$file_name);
}


/**
 * 打印日志函数
 */
function debug(){
    $args = func_get_args();
    $str = '';
    foreach($args as $argv){
        if(is_string($argv)){
            $str .= ' '.$argv;
        }else{
            $str .= ' '.json_encode($argv);
        }
    }
    $time = date('[Y-m-d H:i:s]');
    $backtrace = debug_backtrace();
    $backtrace_line = array_shift($backtrace);
    $backtrace_call = array_shift($backtrace);
    $file = substr($backtrace_line['file'], strlen($_SERVER['DOCUMENT_ROOT']));
    $line = $backtrace_line['line'];
    $class = isset($backtrace_call['class']) ? $backtrace_call['class'] : '';
    $type = isset($backtrace_call['type']) ? $backtrace_call['type'] : '';
    $func = $backtrace_call['function'];
    $info = "$time $file:$line $class$type$func $str".SYSTEM_CRLF;
    error_log($info,3,LOG_FILE);
}


/**
 * 行输出函数
 * @param $str
 */
function echoLine($str){
    if(is_array($str)){
        print_r($str);
        return ;
    }
    if(!is_string($str)){
        $str = json_encode($str);
    }
    echo "$str".SYSTEM_CRLF;
}


/**
 * 解析获取文件后缀名
 * @param $file_path
 * @return false|int|string
 */
function fileExtention($file_path)
{
    $finfo = finfo_open(FILEINFO_MIME);
    $mime = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    $file_type = explode(';', $mime)[0];
    $mimes = getMimes();
    return array_search($file_type, $mimes);
}

/**
 * GBK页面可改为gb2312，其他随意填写为UTF8
 * @param $_String
 * @param string $_Code
 * @return mixed
 */
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
function isWeixin(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
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
        return "获取访客操作系统信息失败！";
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
    if ($tip == "127.0.0.1") {
        return getLocalIp();
    } else {
        return $tip;
    }
}



/**
 * 获取ip的地址信息
 * @param string $ip
 * @return string
 */
function getAddress($ip)
{
    $info = file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=$ip");
    $info = json_decode($info,true);
    return $info['data'];
}


/**
 * 根据ip地址获取城市信息
 * @return array
 */
function getLocalAddress(){
    $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
    $json = file_get_contents($url);
    $address = json_decode($json);
    $info = [];
    if(isset($address->country)){
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
 * @param $city_name
 * @return bool|mixed|string
 */
function getWeatherInfo($city_name){
    $pinyin = pinyin($city_name);
    $city_code = getCityCode($pinyin);
    $url = "http://wthrcdn.etouch.cn/weather_mini?citykey=$city_code";
    $info = file_get_contents($url);
    $info = gzdecode($info);
    $info = json_decode($info,true);
    return $info;
}


/**
 * 手机归属地运营商信息查询
 * @param $mobile
 * @return array
 */
function getMobileInfo($mobile){
    $url = "https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=$mobile";
    $info = file_get_contents($url);
    $info = trim(explode('=',$info)[1]);
    $info = preg_replace('/[\t\r\n \{\}]+/','',$info);
    $info = explode(',',$info);
    $temp = [];
    foreach($info as $value){
        $key_value = explode(':',$value);
        $temp[$key_value[0]] = trim(iconv('GB2312','UTF-8',$key_value[1]),'\'');
    }
    return $temp;
}


/**
 * 接口数据签名
 * @param array $data
 * @return string
 */
function signDate($data=[],$secret)
{
    ksort($data);
    $signStr = [];
    foreach ($data as $key => $val){
        $signStr[] = $key . $val;
    }
    $signStr = implode('&',$signStr);
    $signature = md5($secret . strtolower($signStr) . $secret);
    return $signature;
}


/**
 * 接口验证签名
 * @return array
 */
function validSign($secret){
    $params = $_REQUEST;
    if ($params) {
        $current_time = time();
        $signature = $params['signature'];
        if($signature){
            unset($params['signature']);
        }else{
            return false;
        }
        //允许3分钟的延迟
        if($params['timestamp']>$current_time || $params['timestamp']<$current_time-180){
            return false;
        }
        $sign = signDate($params,$secret);
        if ($signature == $sign) {
            return true;
        }
    }
    return false;
}

/**
 * 全角字符转变成半角字符
 * @param $str
 * @return mixed
 */
function replace_DBC2SBC($str) {
    $DBC = Array(
        '０' , '１' , '２' , '３' , '４' ,
        '５' , '６' , '７' , '８' , '９' ,
        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
        'ｙ' , 'ｚ' , '－' , '　' , '：' ,
        '。' , '，' , '／' , '％' , '＃' ,
        '！' , '＠' , '＆' , '（' , '）' ,
        '＜' , '＞' , '＂' , '＇' , '？' ,
        '［' , '］' , '｛' , '｝' , '＼' ,
        '｜' , '＋' , '＝' , '＿' , '＾' ,
        '￥' , '￣' , '｀' , '“' , '”',
        '；' , '·'
    );
    $SBC = Array(
        '0', '1', '2', '3', '4',
        '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y',
        'Z', 'a', 'b', 'c', 'd',
        'e', 'f', 'g', 'h', 'i',
        'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x',
        'y', 'z', '-', ' ', ':',
        '.', ',', '/', '%', '#',
        '!', '@', '&', '(', ')',
        '<', '>', '"', '\'','?',
        '[', ']', '{', '}', '\\',
        '|', '+', '=', '_', '^',
        '$', '~', '`', '"', '"',
        ';', '.'
    );
    return str_replace($DBC, $SBC, $str);
}

function hashCode($str) {
    $str = (string)$str;
    $hash = 0;
    $len = strlen($str);
    if ($len == 0 )
        return $hash;

    for ($i = 0; $i < $len; $i++) {
        $h = $hash << 5;
        $h -= $hash;
        $h += ord($str[$i]);
        $hash = $h;
        $hash &= 0xFFFFFFFF;
    }
    return $hash;
}

function simHashCode($str){
    return decbin(hashCode($str));
}


function simHash($text){
    $so = scws_new();
    $so->set_charset('utf8'); //编码
    $so->set_duality(0);  //散字二元
    $so->set_ignore(0); //忽略标点符号
    $so->set_multi(0);
    $str = replace_DBC2SBC($text);
    //过滤字符
    $filter = [',','.','-','_','`','、','"',"'",":",';'];
    $so->send_text($str);
    $hash = [];
    while($words = $so->get_result())
    {
        foreach($words as $word){
            $s = $word['word'];
            $weight = intval($word['idf']);
            $hash_code = hashCode($s);
            if(!in_array($s,$filter) && strlen($hash_code)>=6 && $weight){
                $code = decbin($hash_code);
                $code = str_repeat('0',32 - strlen($code)).$code;
                for($i=1;$i<=32;$i++){
                    $value = intval($code[$i]);
                    if($value){
                        $hash[$i] += $weight;
                    }else{
                        $hash[$i] -= $weight;
                    }
                }
            }
        }
    }
    for($i=1;$i<=32;$i++){
        $hash[$i] = intval($hash[$i])>0 ? 1 : 0 ;
    }
    return implode('',$hash);
}

//levenshtein($sim_hash1,$sim_hash2); 用来计算两者之间的距离

//require "curl/Curl.php";
//$curl = new Curl("http://news.ifeng.com/a/20180107/54893174_0.shtml");
//$content = $curl->getContent();
//echo $content;die;

$content1 = "对中央委员会成员和省部级主要领导干部提出了信念过硬、政治过硬、立足于共产党人须臾不忘的初心使命，与政治自觉。，党领导人民进行伟大社会革命才不断取得胜利。在新时代、伟大梦想，党的建设新的伟大工程起着决定性作用。贯彻党的十九大报告提出的新时代党的建设总要求，以党的自我革命来推动伟大社会革命，这既是我们党作为马克思主义政党建设和发展的内在需要，也是我们党领导人民进行伟大社会革命的客观要求。敢于自我革命，我们党才能让自身始终过硬。必须看到，决胜全面建成小康社会的艰巨任务、实现中华民族伟大复兴的历史使命，对我们党提出了前所未有的新挑战新要求，影响党的先进性、弱化党的纯洁性的各种因素具有很强的危险性和破坏性。这就决定了新时代党的建设新的伟大工程，一个极为重要的方面就是要发挥彻底的自我革命精神。全党同志要深刻牢记习近平总书记提出的“四个不容易”：“功成名就时做到居安思危、保持创业初期那种励精图治的精神状态不容易，执掌政权后做到节俭内敛、敬终如始不容易，承平时期严以治吏、防腐戒奢不容易，重大变革关头顺乎潮流、顺应民心不容易”，要深刻懂得我们这样一个有8900多万名党员、450多万个基层党组织的大党，能打败我们的只有我们自己。只有敢于刀刃向内，敢于刮骨疗伤，敢于壮士断腕，才能防止祸起萧墙，让自身始终过硬，始终成为时代先锋、民族脊梁，始终成为马克思主义执政党。以自我革命的精神把党建设好，必须抓住“关键少数”。习近平总书记提出的信念过硬、政治过硬、责任过硬、能力过硬、作风过硬5点要求，是对中央委员会成员和省部级主要领导干部提出的，也是对全党同志特别是各级领导干部的期望。只要我们坚定理想信念，始终把人民群众放在心里，牢固树立“四个意识”，发扬更加强烈的担当精神，全面增强领导能力和执政水平，在服务人民中不断完善自己，我们党就会拥有无比强大力量，经得起各种风浪考验，始终受到人民衷心拥护。办好中国的事情，关键在党。认真学习贯彻习近平总书记“1·5”重要讲话精神，一以贯之推进党的建设新的伟大工程，我们就一定能把党建设得更加坚强有力，夺取新时代坚持和发展中国特色社会主义这场伟大社会革命的新胜利";
$content3 = "对中央委员会成员和省部级主要领导干部提出了信念过硬、政治过硬、责任过硬、能力过硬、作风过硬的5点要求。深邃的历史视野，深沉的忧患意识，深刻的思想境界，立足于共产党人须臾不忘的初心使命，彰显了大国大党领袖的胸襟格局，体现了马克思主义执政党的自我革命勇气与政治自觉。勇于自我革命，从严管党治党，是我们党最鲜明的品格。回望党的97年历史，正是坚持自我革命、加强自身建设，我们党才始终走在时代前列，党领导人民进行伟大社会革命才不断取得胜利。在新时代，坚持和发展中国特色社会主义，党的领导是根本保证；统揽伟大斗争、伟大工程、伟大事业、伟大梦想，党的建设新的伟大工程起着决定性作用。贯彻党的十九大报告提出的新时代党的建设总要求，以党的自我革命来推动伟大社会革命，这既是我们党作为马克思主义政党建设和发展的内在需要，也是我们党领导人民进行伟大社会革命的客观要求。敢于自我革命，我们党才能让自身始终过硬。必须看到，决胜全面建成小康社会的艰巨任务、实现中华民族伟大复兴的历史使命，对我们党提出了前所未有的新挑战新要求，影响党的先进性、弱化党的纯洁性的各种因素具有很强的危险性和破坏性。这就决定了新时代党的建设新的伟大工程，一个极为重要的方面就是要发挥彻底的自我革命精神。全党同志要深刻牢记习近平总书记提出的“四个不容易”：“功成名就时做到居安思危、保持创业初期那种励精图治的精神状态不容易，执掌政权后做到节俭内敛、敬终如始不容易，承平时期严以治吏、防腐戒奢不容易，重大变革关头顺乎潮流、顺应民心不容易”，要深刻懂得我们这样一个有8900多万名党员、450多万个基层党组织的大党，能打败我们的只有我们自己。只有敢于刀刃向内，敢于刮骨疗伤，敢于壮士断腕，才能防止祸起萧墙，让自身始终过硬，始终成为时代先锋、民族脊梁，始终成为马克思主义执政党。以自我革命的精神把党建设好，必须抓住“关键少数”。习近平总书记提出的信念过硬、政治过硬、责任过硬、能力过硬、作风过硬5点要求，是对中央委员会成员和省部级主要领导干部提出的，也是对全党同志特别是各级领导干部的期望。只要我们坚定理想信念，始终把人民群众放在心里，牢固树立“四个意识”，发扬更加强烈的担当精神，全面增强领导能力和执政水平，在服务人民中不断完善自己，我们党就会拥有无比强大力量，经得起各种风浪考验，始终受到人民衷心拥护。办好中国的事情，关键在党。认真学习贯彻习近平总书记“1·5”重要讲话精神，一以贯之推进党的建设新的伟大工程，我们就一定能把党建设得更加坚强有力，夺取新时代坚持和发展中国特色社会主义这场伟大社会革命的新胜利";
$content2 = "我国科技界要坚定创新自信，坚定敢为天下先的志向，在独创独有上下功夫，勇于挑战最前沿的科学问题，提出更多原创理论，作出更多原创发现，力争在重要科技领域实现跨越发展，跟上甚至引领世界科技发展新方向，掌握新一轮全球科技竞争的战略主动。";

$sim_hash1 = simHash($content1);
$sim_hash2 = simHash($content3);

echo time()."<br/>";
for($i=0;$i<=1000000;$i++){
    levenshtein($sim_hash1,$sim_hash2);
}
echo time()."<br/>";
