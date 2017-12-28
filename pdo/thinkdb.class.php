<?php
/* 以下常量必须定义 */
define('DB_TYPE'   , 'pdo');
define('DB_NAME'   , 'test');
define('DB_PREFIX' , '');
define('DB_HOST'   , '192.168.140.183');
define('DB_USER'   , 'root');
define('DB_PWD'    , '123456');
define('DB_PORT'   , '3306');
define('DB_PARAMS' , '');
define('DB_DSN'    , '');
define('DB_LIKE_FIELDS' , 'title|remark');
/*  常量定义结束 */

if(!function_exists('print_x')){
    function print_x(){
        header('Content-type:text/html');
        $ua      = strtolower($_SERVER['HTTP_USER_AGENT']);
        $b       = 0;
        if(stripos($ua, 'browser') !== false) $b = 1;
        $numargs = func_num_args();
        $arg_list = func_get_args();
        for ($i = 0; $i < $numargs; $i++) {
            echo $b ? '<fieldset><legend>参数：'.($i+1).'</legend><pre>' : '参数'.($i+1).'：';
            empty($arg_list[$i]) ? var_dump($arg_list[$i]) : print_r($arg_list[$i]);
            echo $b ? '</pre></fieldset>' : PHP_EOL;
        }
        exit;
    }
}

function send_cache_in_seconds($sec = 5, $reWriteCacheControl = false)
{
    if (!is_numeric($sec) || intval($sec) <= 0) {
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0", true);
        header("Expires: " . gmdate("r", 0) . " GMT", true);
        header("Pragma: no-cache", true);
        return;
    }
    if ($reWriteCacheControl) {
        header("Cache-Control: max-age=" . $sec, true);
    }
    header("Expires: " . gmdate("r", time() + $sec) . " GMT", true);
    header("Pragma: Pragma", true);
}
function logs($message = '', $type=''){
    if(empty($message)){
        return ;
    }
    $log_dir = '../Runtime/log/'.date('Y-m');
    if(!file_exists($log_dir)){
        mkdir($log_dir, 0700,  true);
    }
    if(in_array($type, ['err','sql'])){
        $log_file = $log_dir.'/'.$type.'.log';
    }else{
        $log_file = $log_dir.'/'.date('d').'.log';
    }

    $error_date = date("Y-m-d H:i:s", time());
    if(!empty($_SERVER['HTTP_REFERER'])) {
        $file = $_SERVER['HTTP_REFERER'];
    } else {
        $file = $_SERVER['REQUEST_URI'];
    }
    $res_real = "[$error_date]----[$file]----";
    if(is_array($message)) {
        //error_log($res_real, 3, $log_file);
        $message = var_export($message,true);
        $message = '----['.$message.']'."\n";
        error_log($res_real.$message, 3, $log_file);
    } else {
        $res_real .= "----[$message]\n";
        error_log($res_real, 3, $log_file);
    }
}

/**
 * 取得对象实例 支持调用类的静态方法
 * @param string $name 类名
 * @param string $method 方法名，如果为空则返回实例化对象
 * @param array $args 调用参数
 * @return object
 */
if(!function_exists('get_instance_of')) {
    function get_instance_of($name, $method = '', $args = array())
    {
        static $_instance = array();
        $identify = empty($args) ? $name . $method : $name . $method . md5(json_encode($args));
        if (!isset($_instance[$identify])) {
            if (class_exists($name)) {
                $o = new $name();
                if (method_exists($o, $method)) {
                    if (!empty($args)) {
                        $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                    } else {
                        $_instance[$identify] = $o->$method();
                    }
                } else
                    $_instance[$identify] = $o;
            } else
                print_x( '_CLASS_NOT_EXIST_:' . $name);
        }
        return $_instance[$identify];
    }
}

/**
 * M函数用于实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'Model';
    }
    $guid = (is_array($connection) ? implode('', $connection) : $connection) . $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}
/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/',function ($match){return strtoupper($match[1]);},$name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}
/**
 * ThinkPHP 数据库中间层实现类
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class Db {
    // 数据库类型
    protected $dbType     = null;
    // 是否自动释放查询结果
    protected $autoFree   = false;
    // 当前操作所属的模型名
    protected $model      = '_think_';
    // 是否使用永久连接
    protected $pconnect   = false;
    // 当前SQL指令
    protected $queryStr   = '';
    protected $modelSql   = array();
    // 最后插入ID
    protected $lastInsID  = null;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 返回字段数
    protected $numCols    = 0;
    // 事务指令数
    protected $transTimes = 0;
    // 事务链接ID
    protected $transLinkId = null;
    // 错误信息
    protected $error      = '';
    // 数据库连接ID 支持多个连接
    protected $linkID     = array();
    // 当前连接ID
    protected $_linkID    = null;
    // 当前查询ID
    protected $queryID    = null;
    // 是否已经连接数据库
    protected $connected  = false;
    // 数据库连接参数配置
    protected $config     = '';
    // 数据库表达式
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
    // 查询表达式
    protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';
    // 参数绑定
    protected $bind       = array();

    protected $traceCfg="";

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @return mixed 返回数据库驱动类
     */
    public static function getInstance() {
        $args = func_get_args();
        return get_instance_of(__CLASS__,'factory',$args);
    }

    /**
     * 加载数据库 支持配置文件或者 DSN
     * @access public
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    public function factory($db_config='') {
        // 读取数据库配置
        $db_config = $this->parseConfig($db_config);
        if(empty($db_config['dbms']))
            print_x('_NO_DB_CONFIG_');
        // 数据库类型
        $this->dbType = ucwords(strtolower($db_config['dbms']));
        $class = 'Db'. $this->dbType;
        // 检查驱动类
        if(class_exists($class)) {
            $db = new $class($db_config);
            // 获取当前的数据库类型
            if( 'pdo' != strtolower($db_config['dbms']) )
                $db->dbType = strtoupper($this->dbType);
            else
                $db->dbType = $this->_getDsnType($db_config['dsn']);
        }else {
            // 类没有定义
            print_x('_NO_DB_DRIVER_: ' . $class);
        }
        return $db;
    }

    /**
     * 根据DSN获取数据库类型 返回大写
     * @access protected
     * @param string $dsn  dsn字符串
     * @return string
     */
    protected function _getDsnType($dsn) {
        $match  =  explode(':',$dsn);
        $dbType = strtoupper(trim($match[0]));
        return $dbType;
    }

    /**
     * 分析数据库配置信息，支持数组和DSN
     * @access private
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    private function parseConfig($config='') {
        if ( !empty($config) && is_string($config)) {
            // 如果DSN字符串则进行解析
            $db_config = $this->parseDSN($config);
        }elseif(is_array($config)) { // 数组配置
            $config    =   array_change_key_case($config);
            $db_config = array(
                'dbms'      =>  $config['db_type'],
                'username'  =>  $config['db_user'],
                'password'  =>  $config['db_pwd'],
                'hostname'  =>  $config['db_host'],
                'hostport'  =>  $config['db_port'],
                'database'  =>  $config['db_name'],
                'dsn'       =>  $config['db_dsn'],
                'params'    =>  $config['db_params']
            );
        }elseif(empty($config)) {//去掉
            // 如果配置为空，读取配置文件设置
            if(  DB_DSN  && 'pdo' != strtolower( DB_TYPE ) ) { // 如果设置了DB_DSN 则优先
                $db_config =  $this->parseDSN( DB_DSN );
            }else{
                $db_config = array (
                    'dbms'      =>  DB_TYPE,
                    'username'  =>  DB_USER,
                    'password'  =>  DB_PWD,
                    'hostname'  =>  DB_HOST,
                    'hostport'  =>  DB_PORT,
                    'database'  =>  DB_NAME,
                    'dsn'       =>  DB_DSN,
                    'params'    =>  DB_PARAMS,
                );
            }
        }
        $db_config['deploy_type'] = isset($config['db_deploy_type']) ? $config['db_deploy_type'] : 0;
        $db_config['rw_separate'] = isset($config['db_rw_separate']) ? $config['db_rw_separate'] : false;

        $db_config['rw_master_only'] = isset($config['db_rw_master_only']) ? $config['db_rw_master_only'] : false;

        $db_config['master_num']     = isset($config['db_master_num']) ? $config['db_master_num'] : 1;
        $db_config['slave_no']       = isset($config['db_slave_no']) ? $config['db_slave_no'] : '';

        return $db_config;
    }

    /**
     * @return null
     */
    public function getTrnasLinkId()
    {
        return $this->trnasLinkId;
    }

    /**
     * @param null $trnasLinkId
     */
    public function setTrnasLinkId($trnasLinkId)
    {
        $this->trnasLinkId = $trnasLinkId;
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function initConnect($master=true) {
        //读写分离数据库配置
        if ($this->config['deploy_type'] == 1){
            $this->_linkID = $this->multiConnect($master);
        }else{
            $this->_linkID = $this->singleConnect();
        }
    }

    protected function singleConnect(){
        if (!$this->connected){
            $this->traceCfg             = $this->config;
            $this->traceCfg['link_num'] = 0;
            $this->_linkID              = $this->connect();
        }
        return $this->_linkID;
    }

    /**
     * 连接分布式服务器
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function multiConnect($master=false) {
        $_config = array();
        // 分布式数据库配置解析
        foreach ($this->config as $key=>$val){
            //多数据库支持配置为数组，预防账号或密码存在,引起存在的解析错误 fixed by zeli
            if (is_array($val)){
                $_config[$key] = $val;
            }else{
                $_config[$key] = explode(',',$val);
            }
        }

        // 数据库读写是否分离
        if($this->config['rw_separate']){
            // 主从式采用读写分离
            if($master)
                // 主服务器写入
                $r  =   floor(mt_rand(0, $this->config['master_num'] - 1));
            else{
                if(is_numeric($this->config['slave_no'])) {// 指定服务器读
                    $r = $this->config['slave_no'];
                }else{
                    // 读操作连接从服务器
                    $r = floor(mt_rand($this->config['master_num'], count($_config['hostname'])-1));   // 每次随机连接的数据库
                }
            }
        }else{
            // 读写操作不区分服务器
            if($this->config['rw_master_only']){
                $r = 0;
            }else{
                $r = floor(mt_rand(0,count($_config['hostname'])-1));   // 每次随机连接的数据库
            }
        }
        $db_config = array(
            'username'  =>  isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'  =>  isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'hostname'  =>  isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'hostport'  =>  isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'  =>  isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'       =>  isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'params'    =>  isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
        );
        $this->traceCfg = $db_config;
        $this->traceCfg['dbms']        = $this->config['dbms'];
        $this->traceCfg['deploy_type'] = $this->config['deploy_type'];
        $this->traceCfg['link_num']    = $r;
        return $this->connect($db_config,$r);
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName
     * @static
     * @access public
     * @param string $dsnStr
     * @return array
     */
    public function parseDSN($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info    = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
                'dbms'      =>  $info['scheme'],
                'username'  =>  isset($info['user']) ? $info['user'] : '',
                'password'  =>  isset($info['pass']) ? $info['pass'] : '',
                'hostname'  =>  isset($info['host']) ? $info['host'] : '',
                'hostport'  =>  isset($info['port']) ? $info['port'] : '',
                'database'  =>  isset($info['path']) ? substr($info['path'],1) : ''
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
                'dbms'      =>  $matches[1],
                'username'  =>  $matches[2],
                'password'  =>  $matches[3],
                'hostname'  =>  $matches[4],
                'hostport'  =>  $matches[5],
                'database'  =>  $matches[6]
            );
        }
        $dsn['dsn'] =  ''; // 兼容配置信息数组
        return $dsn;
    }

    /**
     * 数据库调试 记录当前SQL
     * @access protected
     */
    protected function debug() {
        $this->modelSql[$this->model]   =  $this->queryStr;
        $this->model  =   '_think_';
        // 记录操作结束时间 // 去掉
        //if (C('DB_SQL_LOG')) {
        //    G('queryEndTime');
        //    $runtime = G('queryStartTime', 'queryEndTime', 6);
        //    $dbStr = "db_type:[ ".$this->traceCfg["dbms"]." ],db_hosts:[ ".$this->traceCfg["hostname"].":".$this->traceCfg["hostport"]." ],"."\r\n"."db_name:[ ".$this->traceCfg["database"]." ]";
        //    if ($this->traceCfg['deploy_type']){
        //        $dbStr .= ",deploy_type:[ multi ], hit: [ {$this->traceCfg['link_num']} ]";
        //    }else{
        //        $dbStr .= ",deploy_type:[ single ]";
        //    }
        //    trace(my_debug_backtrace("sql").$dbStr."\r\n ".$this->queryStr . ' [ RunTime:' . $runtime . 's ]', '', 'SQL');
        //}
    }

    /**
     * 设置锁机制
     * @access protected
     * @return string
     */
    protected function parseLock($lock=false) {
        if(!$lock) return '';
        if('ORACLE' == $this->dbType) {
            return ' FOR UPDATE NOWAIT ';
        }
        return ' FOR UPDATE ';
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $set[]  =   $this->parseKey($key).'='.$val[1];
            }elseif(is_scalar($val) || is_null(($val))) { // 过滤非标量数据
                //if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                if(0 !== strpos($val,':')){//去掉
                    $name   =   md5($key);
                    $set[]  =   $this->parseKey($key).'=:'.$name;
                    $this->bindParam($name,$val);
                }else{
                    $set[]  =   $this->parseKey($key).'='.$this->parseValue($val);
                }
            }
        }
        return ' SET '.implode(',',$set);
    }

    /**
     * 参数绑定
     * @access protected
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     * @return void
     */
    protected function bindParam($name,$value){
        $this->bind[':'.$name]  =   $value;
    }

    /**
     * 参数绑定分析
     * @access protected
     * @param array $bind
     * @return array
     */
    protected function parseBind($bind){
        $bind           =   array_merge($this->bind,$bind);
        $this->bind     =   array();
        return $bind;
    }

    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }

    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function parseField($fields) {
        if(is_string($fields) && strpos($fields,',')) {
            $fields    = explode(',',$fields);
        }
        if(is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array   =  array();
            foreach ($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                else
                    $array[] =  $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        }elseif(is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        }else{
            $fieldsStr = '*';
        }
        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param mixed $table
     * @return string
     */
    protected function parseTable($tables) {
        if(is_array($tables)) {// 支持别名定义
            $array   =  array();
            foreach ($tables as $table=>$alias){
                if(!is_numeric($table))
                    $array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
                else
                    $array[] =  $this->parseKey($table);
            }
            $tables  =  $array;
        }elseif(is_string($tables)){
            $tables  =  explode(',',$tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        return implode(',',$tables);
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组表达式
            $operate  = isset($where['_logic']) ? strtoupper($where['_logic']) : '';
            if(in_array($operate,array('AND','OR','XOR'))){
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.$operate.' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(is_numeric($key)){
                    $key  = '_complex';
                }
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                        print_x('_EXPRESS_ERROR_:'.$key);
                    }
                    // 多条件支持
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array =  explode('|',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array =  explode('&',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    // where子单元分析
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// 模糊查找
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        if(in_array($likeLogic,array('AND','OR','XOR'))){
                            $likeStr    =   $this->comparison[strtolower($val[0])];
                            $like       =   array();
                            foreach ($val[1] as $item){
                                $like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
                            }
                            $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';
                        }
                    }else{
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                    }
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                    //}elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                }elseif(preg_match('/^(NOTIN|IN)$/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                            $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                    //}elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                }elseif(preg_match('/^(NOTBETWEEN|BETWEEN)$/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    print_x('_EXPRESS_ERROR_:'.$val[0]);
                }
            }else {
                $count = count($val);
                $rule  = isset($val[$count-1])?strtoupper($val[$count-1]):'';
                if(in_array($rule,array('AND','OR','XOR'))) {
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
                    if('exp' == strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $op        = is_array($val[$i]) ? $this->comparison[strtolower($val[$i][0])] : '=';
                        $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            //对字符串类型字段采用模糊匹配
            //if(C('DB_LIKE_FIELDS') && preg_match('/^('.C('DB_LIKE_FIELDS').')$/i',$key)) {
            if(preg_match('/^('. DB_LIKE_FIELDS .')$/i',$key)) {//去掉
                $val  =  '%'.$val.'%';
                $whereStr .= $key.' LIKE '.$this->parseValue($val);
            }else {
                $whereStr .= $key.' = '.$this->parseValue($val);
            }
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr   =   is_string($val) ? $val : substr($this->parseWhere($val),6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array    =  array();
                foreach ($where as $field => $data) {
                    $array[] = $this->parseKey($field) . ' = ' . $this->parseValue($data);
                }
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }

    /**
     * limit分析
     * @access protected
     * @param mixed $lmit
     * @return string
     */
    protected function parseLimit($limit) {
        return !empty($limit) ?   ' LIMIT '.$limit.' ' : '';
    }

    /**
     * join分析
     * @access protected
     * @param mixed $join
     * @return string
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if(!empty($join)) {
            if(is_array($join)) {
                foreach ($join as $key => $_join){
                    if(false !== stripos($_join,'JOIN'))
                        $joinStr .= ' '.$_join;
                    else
                        $joinStr .= ' LEFT JOIN ' .$_join;
                }
            }else{
                $joinStr .= ' LEFT JOIN ' .$join;
            }
        }
        //将__TABLE_NAME__这样的字符串替换成正规的表名,并且带上前缀和后缀
        //$joinStr = preg_replace_callback('/__([A-Z_-]+)__/sU',function ($math){return C("DB_PREFIX").strtolower($math[1]);} ,$joinStr );
        $joinStr = preg_replace_callback('/__([A-Z_-]+)__/sU',function ($math){return  DB_PREFIX .strtolower($math[1]);} ,$joinStr );
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $this->parseKey($val);
                }else{
                    $array[] =  $this->parseKey($key).' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order) ?  ' ORDER BY '.$order : '';
    }

    /**
     * group分析
     * @access protected
     * @param mixed $group
     * @return string
     */
    protected function parseGroup($group) {
        return !empty($group) ? ' GROUP BY '.$group : '';
    }

    /**
     * having分析
     * @access protected
     * @param string $having
     * @return string
     */
    protected function parseHaving($having) {
        return  !empty($having) ?   ' HAVING '.$having : '';
    }

    /**
     * comment分析
     * @access protected
     * @param string $comment
     * @return string
     */
    protected function parseComment($comment) {
        return  !empty($comment) ?   ' /* '.$comment.' */' : '';
    }

    /**
     * distinct分析
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct) ?   ' DISTINCT ' : '';
    }

    /**
     * union分析
     * @access protected
     * @param mixed $union
     * @return string
     */
    protected function parseUnion($union) {
        if(empty($union)) return '';
        if(isset($union['_all'])) {
            $str  =   'UNION ALL ';
            unset($union['_all']);
        }else{
            $str  =   'UNION ';
        }
        foreach ($union as $u){
            $sql[] = $str . (is_array($u) ? $this->buildSelectSql($u) : $u);
        }
        return implode(' ', $sql);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            }elseif(is_scalar($val) || is_null(($val))) { // 过滤非标量数据
                $fields[]   =  $this->parseKey($key);
                //if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                if(0 !== strpos($val,':')){//去掉
                    $name       =   md5($key);
                    $values[]   =   ':'.$name;
                    $this->bindParam($name,$val);
                }else{
                    $values[]   =  $this->parseValue($val);
                }
            }
        }
        $sql    =  ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $sql   .= $this->parseLock(isset($options['lock']) ? $options['lock'] : false);
        $sql   .= $this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql,$this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $option  查询数据参数
     * @return false | integer
     */
    public function selectInsert($fields,$table,$options=array()) {
        $this->model  =   $options['model'];
        if(is_string($fields))   $fields    = explode(',', $fields);
        array_walk($fields, array($this, 'parseKey'));
        $sql    =    'INSERT INTO '.$this->parseTable($table).' ('.implode(',', $fields).') ';
        $sql   .= $this->buildSelectSql($options);
        return $this->execute($sql,$this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $sql   = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(!empty($options['where']) ? $options['where'] : '')
            .$this->parseOrder(!empty($options['order']) ? $options['order'] : '')
            .$this->parseLimit(!empty($options['limit']) ? $options['limit'] : '')
            .$this->parseLock(isset($options['lock']) ? $options['lock'] : false)
            .$this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql,$this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function delete($options=array()) {
        $this->model  =   $options['model'];
        $sql   = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(!empty($options['where']) ? $options['where'] : '')
            .$this->parseOrder(!empty($options['order']) ? $options['order'] : '')
            .$this->parseLimit(!empty($options['limit']) ? $options['limit'] : '')
            .$this->parseLock(isset($options['lock']) ? $options['lock'] : false)
            .$this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql, $this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    public function select($options=array()) {
        $this->model  =   $options['model'];
        $sql    = $this->buildSelectSql($options);
        $cache  =  isset($options['XCache']) ? $options['XCache'] : false;
        //if($cache) { // 查询缓存检测
        //  $key    =  is_string($cache['key']) ? $cache['key'] : md5($sql);
        //  $value  =  S($key,'',$cache);//去掉
        //  if(false !== $value) {
        //      return $value;
        //  }
        //}
        $result   = $this->query($sql,$this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
        //if($cache && false !== $result ) { // 查询缓存写入
        //  S($key,$result,$cache);//去掉
        //}
        return $result;
    }

    /**
     * 生成查询SQL
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function buildSelectSql($options=array()) {
        if(isset($options['page'])) {
            // 根据页数计算limit
            if(strpos($options['page'], ',')) {
                list($page,$listRows) =  explode(',',$options['page']);
            }else{
                $page = $options['page'];
            }
            $page     =  $page ? $page : 1;
            $listRows =  isset($listRows) ? $listRows : (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset   =  $listRows * ((int)$page - 1);
            $options['limit'] =  $offset . ',' . $listRows;
        }
        //if(C('DB_SQL_BUILD_CACHE')) { // SQL创建缓存
        //    $key    =  md5(serialize($options));
        //    $value  =  S($key);
        //    if(false !== $value) {
        //        return $value;
        //    }
        //}
        $sql  = $this->parseSql($this->selectSql,$options);
        $sql .= $this->parseLock(isset($options['lock']) ? $options['lock'] : false);
        //if(isset($key)) { // 写入SQL创建缓存
        //   S($key,$sql,array('expire'=>0,'length'=>C('DB_SQL_BUILD_LENGTH'),'queue'=>C('DB_SQL_BUILD_QUEUE')));//去掉
        //}
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function parseSql($sql,$options=array()){
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct']) ? $options['distinct'] : false),
                $this->parseField(!empty($options['field'])      ? $options['field']    : '*'),
                $this->parseJoin(!empty($options['join'])        ? $options['join']     : ''),
                $this->parseWhere(!empty($options['where'])      ? $options['where']    : ''),
                $this->parseGroup(!empty($options['group'])      ? $options['group']    : ''),
                $this->parseHaving(!empty($options['having'])    ? $options['having']   : ''),
                $this->parseOrder(!empty($options['order'])      ? $options['order']    : ''),
                $this->parseLimit(!empty($options['limit'])      ? $options['limit']    : ''),
                $this->parseUnion(!empty($options['union'])      ? $options['union']    : ''),
                $this->parseComment(!empty($options['comment'])  ? $options['comment']  : '')
            ),$sql);
        return $sql;
    }

    /**
     * 获取最近一次查询的sql语句
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql($model='') {
        return $model ? $this->modelSql[$model] : $this->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model  模型名
     * @return void
     */
    public function setModel($model){
        $this->model =  $model;
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 释放查询
        if ($this->queryID){
            $this->free();
        }
        // 关闭连接
        $this->close();
    }

    // 关闭数据库 由驱动类定义
    public function close(){}
}
?>
<?php

/**
 * PDO数据库驱动
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Db
 * @author    liu21st <liu21st@gmail.com>
 */
class DbPdo extends Db{

    protected $PDOStatement = null;
    private   $table        = '';
    protected $dbType       = null;
    protected $transLinkId  = null;
    /**
     * @var PDO $_linkID
     */
    protected $_linkID      = null;

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config= array()){
        if ( !class_exists('PDO') ) {
            print_x('_NOT_SUPPERT_:PDO');
        }
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
        }
    }

    /**
     * 连接数据库方法
     * @access public
     * @param array $config
     * @param int $linkNum
     */
    public function connect($config=array(),$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            if($this->pconnect) {
                $config['params'][PDO::ATTR_PERSISTENT] = true;
            }
            //$config['params'][PDO::ATTR_CASE] = C("DB_CASE_LOWER")?PDO::CASE_LOWER:PDO::CASE_UPPER;
            //添加无需DSN配置支持
            if( !isset( $config['dsn'] ) || empty( $config['dsn'] ) ){
                $config['dsn'] = "mysql:host=".$config['hostname'].";port=".$config['hostport'].";dbname=".$config['database'];
            }
            try{
                $this->linkID[$linkNum] = new PDO( $config['dsn'], $config['username'], $config['password'], $config['params']);
            }catch (PDOException $e) {
                $msg = sprintf("DB_USER: %s, DB_DSN: %s", $config['username'],$config['dsn']);
                print_x("DB_PDO_mysql_connect ".$e->getMessage()."\r\n".$msg."\r\n");
            }
            // 因为PDO的连接切换可能导致数据库类型不同，因此重新获取下当前的数据库类型
            $this->dbType = $this->_getDsnType($config['dsn']);
            if(in_array($this->dbType,array('MSSQL','ORACLE','IBASE','OCI'))) {
                // 由于PDO对于以上的数据库支持不够完美，所以屏蔽了 如果仍然希望使用PDO 可以注释下面一行代码
                print_x('由于目前PDO暂时不能完美支持'.$this->dbType.' 请使用官方的'.$this->dbType.'驱动');
            }
            $this->linkID[$linkNum]->exec('SET NAMES utf8');//去掉.C('DB_CHARSET')
            // 标记连接成功
            $this->connected    =   true;
            // 注销数据库连接配置信息
            if(1 != $this->config['deploy_type']) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     * 根据DSN获取数据库类型 返回大写
     * @access protected
     * @param string $dsn  dsn字符串
     * @return string
     */
    protected function _getDsnType($dsn) {
        $match  = explode(':',$dsn);
        $dbType = strtoupper(trim($match[0]));
        return $dbType;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @param array $bind 参数绑定
     * @return mixed
     */
    public function query($str,$bind=array()) {
        // 事务开启的情况下，查询应该选择和事务采用同一个连接，非强制，但是为了防止开发人员和框架意外的调用了query，
        // 也为了避免读写数据不一致
        if($this->transTimes>0){
            $this->_linkID = $this->transLinkId;
        }else{
            $this->initConnect(false);
        }
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($bind)){
            $this->queryStr     .=   '[ '.print_r($bind,true).' ]';
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        //N('db_query',1);
        //// 记录开始执行时间
        //G('queryStartTime');
        //$event = new \Juanpi\Model\Events\DoctrineEvent();
        //$dsn = "mysql://{$this->traceCfg['hostname']}:{$this->traceCfg['hostport']}?db={$this->traceCfg['database']}&method=query";
        //$event->setMethod($_SERVER["REQUEST_URI"]);
        //$event->setArgs($dsn." ".$str);
        ///**
        // * @var \Symfony\Component\DependencyInjection\ContainerBuilder $container
        // */
        //$container= getContainerBuilder();
        ///**
        // * @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
        // */
        //$eventDispatcher = $container->get("kernel.event_dispatcher");
        //$eventDispatcher->dispatch(\Juanpi\Model\Classes\DoctrineEvents::STARTQUERY,$event);
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            print_x($this->error());
        }
        $result =   $this->PDOStatement->execute($bind);
        //$eventDispatcher->dispatch(\Juanpi\Model\Classes\DoctrineEvents::ENDQUERY,$event);
        $this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $this->getAll();
        }
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @param array $bind 参数绑定
     * @return integer
     */
    public function execute($str,$bind=array()) {
        //事务开启的情况下，执行应该选择和事务采用同一个连接
        if($this->transTimes>0){
            $this->_linkID = $this->transLinkId;
        }else{
            $this->initConnect(true);
        }
        if ( !$this->_linkID ) return false;
        $this->queryStr    = $str;
        if(!empty($bind)){
            $this->queryStr     .=   '[ '.print_r($bind,true).' ]';
        }
        $flag = false;
        if($this->dbType == 'OCI')
        {
            if(preg_match("/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i", $this->queryStr, $match)) {
                //$this->table = C("DB_SEQUENCE_PREFIX").str_ireplace(C("DB_PREFIX"), "", $match[2]);
                $this->table = str_ireplace( DB_PREFIX , "", $match[2]);//去掉
                $flag = (boolean)$this->query("SELECT * FROM user_sequences WHERE sequence_name='" . strtoupper($this->table) . "'");
            }
        }//modify by wyfeng at 2009.08.28
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        //N('db_write',1);
        // 记录开始执行时间
        //G('queryStartTime');
        //$event = new \Juanpi\Model\Events\DoctrineEvent();
        //$dsn = "mysql://{$this->traceCfg['hostname']}:{$this->traceCfg['hostport']}?db={$this->traceCfg['database']}&method=execute";
        //$event->setMethod($_SERVER["REQUEST_URI"]);
        //$event->setArgs($dsn." ".$str);
        ///**
        // * @var \Symfony\Component\DependencyInjection\ContainerBuilder $container
        // */
        //$container= getContainerBuilder();
        ///**
        // * @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
        // */
        //$eventDispatcher = $container->get("kernel.event_dispatcher");
        //$eventDispatcher->dispatch(\Juanpi\Model\Classes\DoctrineEvents::STARTQUERY,$event);
        $this->PDOStatement =   $this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            print_x($this->error());
        }
        $result =   $this->PDOStatement->execute($bind);
        //$eventDispatcher->dispatch(\Juanpi\Model\Classes\DoctrineEvents::ENDQUERY,$event);
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = $this->PDOStatement->rowCount();
            if($flag || preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                $this->lastInsID = $this->getLastInsertId();
            }
            return $this->numRows;
        }
    }

    /**
     * 启动事务
     * @access public
     * @return void|boolean
     */
    public function startTrans() {
        if($this->transTimes>0){
            $this->_linkID = $this->transLinkId;
            if ( !$this->_linkID ) return false;return true;
        }else{
            $this->initConnect(true);
            if ( !$this->_linkID ) return false;
            $this->transLinkId = $this->_linkID;
        }
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $this->_linkID->beginTransaction();
        }
        $this->transTimes++;
        return true;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolean
     */
    public function commit() {
        if ($this->transTimes > 0) {
            // 提交事务时强制切换到开启事务的连接
            $this->_linkID     = $this->transLinkId;
            if ( !$this->_linkID ) return false;
            $result            = $this->_linkID->commit();
            $this->transTimes  = 0;
            $this->transLinkId = null;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            // 回滚事务时，强制切换到开启事务的连接
            $this->_linkID     = $this->transLinkId;
            if ( !$this->_linkID ) return false;
            $result            = $this->_linkID->rollBack();
            $this->transTimes  = 0;
            $this->transLinkId = null;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getAll() {
        //返回数据集
        $result        = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        $this->numRows = count( $result );
        return $result;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     */
    public function getFields($tableName) {
        $this->initConnect(true);
        //if(C('DB_DESCRIBE_TABLE_SQL')) {//去掉
        //    // 定义特殊的字段查询SQL
        //    $sql   = str_replace('%table%',$tableName,C('DB_DESCRIBE_TABLE_SQL'));
        //}else{
        switch($this->dbType) {
            case 'MSSQL':
            case 'SQLSRV':
                $sql   = "SELECT   column_name as 'Name',   data_type as 'Type',   column_default as 'Default',   is_nullable as 'Null'
        FROM    information_schema.tables AS t
        JOIN    information_schema.columns AS c
        ON  t.table_catalog = c.table_catalog
        AND t.table_schema  = c.table_schema
        AND t.table_name    = c.table_name
        WHERE   t.table_name = '$tableName'";
                break;
            case 'SQLITE':
                $sql   = 'PRAGMA table_info ('.$tableName.') ';
                break;
            case 'ORACLE':
            case 'OCI':
                $sql   = "SELECT a.column_name \"Name\",data_type \"Type\",decode(nullable,'Y',0,1) notnull,data_default \"Default\",decode(a.column_name,b.column_name,1,0) \"pk\" "
                    ."FROM user_tab_columns a,(SELECT column_name FROM user_constraints c,user_cons_columns col "
                    ."WHERE c.constraint_name=col.constraint_name AND c.constraint_type='P' and c.table_name='".strtoupper($tableName)
                    ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)";
                break;
            case 'PGSQL':
                $sql   = 'select fields_name as "Name",fields_type as "Type",fields_not_null as "Null",fields_key_name as "Key",fields_default as "Default",fields_default as "Extra" from table_msg('.$tableName.');';
                break;
            case 'IBASE':
                break;
            case 'MYSQL':
            default:
                $sql   = 'DESCRIBE '.$tableName;//备注: 驱动类不只针对mysql，不能加``
        }
        //}
        if(!isset($sql)){
            return array();
        }
        $result = $this->query($sql);
        $info   = array();
        if($result) {
            foreach ($result as $key => $val) {
                $val            =   array_change_key_case($val);
                $val['name']    =   isset($val['name'])  ? $val['name']  : "";
                $val['type']    =   isset($val['type'])  ? $val['type']  : "";
                $name           =   isset($val['field']) ? $val['field'] : $val['name'];
                $info[$name]    =   array(
                    'name'    => $name ,
                    'type'    => $val['type'],
                    'notnull' => (bool)(((isset($val['null'])) && ($val['null'] === '')) || ((isset($val['notnull'])) && ($val['notnull'] === ''))), // not null is empty, null is yes
                    'default' => isset($val['default']) ? $val['default'] : (isset($val['dflt_value']) ? $val['dflt_value'] : ""),
                    'primary' => isset($val['key']) ? strtolower($val['key']) == 'pri' : (isset($val['pk']) ? $val['pk'] : false),
                    'autoinc' => isset($val['extra']) ? strtolower($val['extra']) == 'auto_increment' : (isset($val['key']) ? $val['key'] : false),
                );
            }
        }
        return $info;
    }

    /**
     * 取得数据库的表信息
     * @access public
     * @param string $dbName
     * @return array
     */
    public function getTables($dbName='') {
        //if(C('DB_FETCH_TABLES_SQL')) {
        //    // 定义特殊的表查询SQL
        //    $sql   = str_replace('%db%',$dbName,C('DB_FETCH_TABLES_SQL'));
        //}else{
        switch($this->dbType) {
            case 'ORACLE':
            case 'OCI':
                $sql   = 'SELECT table_name FROM user_tables';
                break;
            case 'MSSQL':
            case 'SQLSRV':
                $sql   = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
                break;
            case 'PGSQL':
                $sql   = "select tablename as Tables_in_test from pg_tables where  schemaname ='public'";
                break;
            case 'IBASE':
                // 暂时不支持
                print_x('_NOT_SUPPORT_DB_:IBASE');
                break;
            case 'SQLITE':
                $sql   = "SELECT name FROM sqlite_master WHERE type='table' "
                    . "UNION ALL SELECT name FROM sqlite_temp_master "
                    . "WHERE type='table' ORDER BY name";
                break;
            case 'MYSQL':
            default:
                if(!empty($dbName)) {
                    $sql    = 'SHOW TABLES FROM '.$dbName;
                }else{
                    $sql    = 'SHOW TABLES ';
                }
        }
        //}
        if(!isset($sql)){
            return array();
        }
        $result = $this->query($sql);
        $info   = array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * limit分析
     * @access protected
     * @param string $limit
     * @return string
     */
    protected function parseLimit($limit) {
        $limitStr    = '';
        if(!empty($limit)) {
            switch($this->dbType){
                case 'PGSQL':
                case 'SQLITE':
                    $limit  =   explode(',',$limit);
                    if(count($limit)>1) {
                        $limitStr .= ' LIMIT '.$limit[1].' OFFSET '.$limit[0].' ';
                    }else{
                        $limitStr .= ' LIMIT '.$limit[0].' ';
                    }
                    break;
                case 'MSSQL':
                case 'SQLSRV':
                    break;
                case 'IBASE':
                    // 暂时不支持
                    break;
                case 'ORACLE':
                case 'OCI':
                    break;
                case 'MYSQL':
                default:
                    $limitStr .= ' LIMIT '.$limit.' ';
            }
        }
        return $limitStr;
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        if($this->dbType == 'MYSQL'){
            $key   =  trim($key);
            if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
                $key = '`'.$key.'`';
            }
            return $key;
        }else{
            return parent::parseKey($key);
        }

    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }

    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1].':'.$error[2];
        }else{
            $this->error = '';
        }
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        //trace($this->error,'','ERR'); //去掉
        return $this->error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL指令
     * @return string
     */
    public function escapeString($str) {
        switch($this->dbType) {
            case 'PGSQL':
            case 'MSSQL':
            case 'SQLSRV':
            case 'MYSQL':
                return addslashes($str);
            case 'IBASE':
            case 'SQLITE':
            case 'ORACLE':
            case 'OCI':
                return str_ireplace("'", "''", $str);
            default:
                return addslashes($str);
        }
    }

    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  strpos($value,':') === 0 ? $this->escapeString($value) : '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * 获取最后插入id
     * @access public
     * @return integer
     */
    public function getLastInsertId() {
        switch($this->dbType) {
            case 'PGSQL':
            case 'SQLITE':
            case 'MSSQL':
            case 'SQLSRV':
            case 'IBASE':
            case 'MYSQL':
                return $this->_linkID->lastInsertId();
            case 'ORACLE':
            case 'OCI':
                $sequenceName = $this->table;
                $vo = $this->query("SELECT {$sequenceName}.currval currval FROM dual");
                return $vo ? $vo[0]["currval"] : 0;
        }
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $datas 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insertAll($datas,$options=array(),$replace=false) {
        if(!is_array($datas[0])) return false;
        $fields = array_keys($datas[0]);
        array_walk($fields, array($this, 'parseKey'));
        $values  =  array();
        foreach ($datas as $data){
            $value   =  array();
            foreach ($data as $key => $val){
                $val   =  $this->parseValue($val);
                if(is_scalar($val)) { // 过滤非标量数据
                    $value[]   =  $val;
                }
            }
            $values[]    = '('.implode(',', $value).')';
        }
        $sql   =  ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES '.implode(',',$values);
        return $this->execute($sql);
    }
}
?>
<?php
/**
 * ThinkPHP Model模型类
 * 实现了ORM和ActiveRecords模式
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class Model
{
    // 操作状态
    const MODEL_INSERT    = 1;      //  插入模型数据
    const MODEL_UPDATE    = 2;      //  更新模型数据
    const MODEL_BOTH      = 3;      //  包含上面两种方式
    const MUST_VALIDATE   = 1;// 必须验证
    const EXISTS_VALIDATE = 0;// 表单存在字段则验证
    const VALUE_VALIDATE  = 2;// 表单值不为空则验证
    // 当前使用的扩展模型
    /**
     * @var Model $_extModel
     */
    private $_extModel    = null;
    // 当前数据库操作对象
    /**
     * @var DB $db
     */
    protected $db          = null;
    // 数据库对象池
    protected $_db         = array();
    // 数据库配置池
    protected $_linkNum    = array();
    // 主键名称
    protected $pk          = 'id';
    // 数据表前缀
    protected $tablePrefix = '';
    // 模型名称
    protected $name        = '';
    // 数据库名称
    protected $dbName      = '';
    //数据库配置
    protected $connection  = '';
    // 数据表名（不包含表前缀）
    protected $tableName   = '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName = '';
    // 最近错误信息
    protected $error         = '';
    // 字段信息
    protected $fields        = array();
    // 数据信息
    protected $data          = array();
    // 查询表达式参数
    protected $options       = array();
    protected $_validate     = array();  // 自动验证定义
    protected $_auto         = array();  // 自动完成定义
    protected $_map          = array();  // 字段映射定义
    protected $_scope        = array();  // 命名范围定义
    // 是否自动检测数据表字段信息
    protected $autoCheckFields = true;
    // 是否批处理验证
    protected $patchValidate   = false;
    // 链操作方法列表
    protected $methods         = array('table', 'order', 'alias', 'having', 'group', 'lock', 'distinct', 'auto', 'filter', 'validate', 'result', 'bind', 'token');

    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @access public
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param string $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        // 模型初始化
        $this->_initialize();
        $_connection = !empty($connection) ? $connection : $this->connection;
        if (is_array($_connection)) {
            $config  = array_change_key_case($_connection, CASE_LOWER);
            //Log::write("The Thired argument require a String, an Array was given, it will be denied soon," . json_encode($_connection));
        } else {

            $_connection['DB_TYPE']   = DB_TYPE;
            $_connection['DB_NAME']   = DB_NAME;
            $_connection['DB_PREFIX'] = DB_PREFIX;
            $_connection['DB_HOST']   = DB_HOST;
            $_connection['DB_USER']   = DB_USER;
            $_connection['DB_PWD']    = DB_PWD;
            $_connection['DB_PORT']   = DB_PORT;

            //$config = array_change_key_case(C($_connection), CASE_LOWER);
            $config = array_change_key_case( $_connection , CASE_LOWER);
        }
        // 获取模型名称
        if (!empty($name)) {
            if (strpos($name, '.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName, $this->name) = explode('.', $name);
            } else {
                if (!empty($_connection) && !is_null($config['db_name'])) {
                    $this->dbName = $config['db_name'];
                } else {
                    //$this->dbName = C("DB_NAME");
                    $this->dbName = DB_NAME;
                }
                $this->name = $name;
            }
        } elseif (empty($this->name)) {
            $this->name = $this->getModelName();
        }
        // 设置表前缀
        if (is_null($tablePrefix)) {// 前缀为Null表示没有前缀
            $this->tablePrefix = '';
        } elseif ('' != $tablePrefix) {
            $this->tablePrefix = $tablePrefix;
        } else {
            //$this->tablePrefix = !empty($_connection) && !is_null($config['db_prefix']) ? $config['db_prefix'] : C('DB_PREFIX');
            $this->tablePrefix = !empty($_connection) && !is_null($config['db_prefix']) ? $config['db_prefix'] : DB_PREFIX;
        }

        // 数据库初始化操作
        // 获取数据库操作对象
        // 当前模型有独立的数据库连接信息
        $this->db(0, $_connection);
    }

    /**
     * 自动检测数据表信息
     * @access protected
     * @return void
     */
    protected function _checkTableInfo()
    {
        // 如果不是Model类 自动记录数据表信息
        // 只在第一次执行记录
        if (empty($this->fields)) {
            // 如果数据表字段没有定义则自动获取
            //if (C('DB_FIELDS_CACHE')) {//去掉
            //    $tableName = parse_name($this->name);
            //    $db = $this->dbName;
            //    $fields = F('_fields/' . strtolower($db . '.' . $tableName));
            //    if ($fields) {
            //        $version = C('DB_FIELD_VERSION');
            //        if (empty($version) || $fields['_version'] == $version) {
            //            $this->fields = $fields;
            //            return;
            //        }
            //    }
            //}
            // 每次都会读取数据表信息
            $this->flush();
        }
    }

    /**
     * 获取字段信息并缓存
     * @access public
     * @return mixed
     */
    public function flush()
    {
        // 缓存不存在则查询数据表信息
        $this->db->setModel($this->name);
        $fields = $this->db->getFields($this->getTableName());
        if (!$fields) { // 无法获取字段信息
            return false;
        }
        $this->fields = array_keys($fields);
        $this->fields['_autoinc'] = false;
        $type = null;
        foreach ($fields as $key => $val) {
            // 记录字段类型
            $type[$key] = $val['type'];
            if ($val['primary']) {
                $this->fields['_pk'] = $key;
                if ($val['autoinc']) $this->fields['_autoinc'] = true;
            }
        }
        // 记录字段类型信息
        $this->fields['_type'] = $type;
        //if (C('DB_FIELD_VERSION')) $this->fields['_version'] = C('DB_FIELD_VERSION');//去掉

        // 2008-3-7 增加缓存开关控制
        //if (C('DB_FIELDS_CACHE')) {//去掉
        //    // 永久缓存数据表信息
        //    $db = $this->dbName ? $this->dbName : C('DB_NAME');
        //    $tableName = parse_name($this->name);
        //    F('_fields/' . strtolower($db . '.' . $tableName), $this->fields);
        //}
        return null;
    }

    /**
     * 动态切换扩展模型
     * @access public
     * @param string $type 模型类型名称
     * @param mixed $vars 要传入扩展模型的属性变量
     * @return Model
     */
    public function switchModel($type, $vars = array())
    {
        $class = ucwords(strtolower($type)) . 'Model';
        if (!class_exists($class))
            print_x($class .  '_MODEL_NOT_EXIST_' );
        // 实例化扩展模型
        $this->_extModel = new $class($this->name);
        if (!empty($vars)) {
            // 传入当前模型的属性到扩展模型
            foreach ($vars as $var)
                $this->_extModel->setProperty($var, $this->$var);
        }
        return $this->_extModel;
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value)
    {
        // 设置数据对象属性
        $this->data[$name] = $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (in_array(strtolower($method), $this->methods, true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] = $args[0];
            return $this;
        } elseif (in_array(strtolower($method), array('count', 'sum', 'min', 'max', 'avg'), true)) {
            // 统计查询的实现
            $field = isset($args[0]) ? $args[0] : '*';
            return $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
        } elseif (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field = parse_name(substr($method, 5));
            $where[$field] = $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name = parse_name(substr($method, 10));
            $where[$name] = $args[0];
            return $this->where($where)->getField($args[1]);
        } elseif (isset($this->_scope[$method])) {// 命名范围的单独调用支持
            return $this->scope($method, $args[0]);
        } else {
            print_x(__CLASS__ . ':' . $method . '_METHOD_NOT_EXIST_' );
            return null;
        }
    }

    // 回调方法 初始化模型
    protected function _initialize()
    {
    }

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _facade($data)
    {
        // 检查非数据字段
        if (!empty($this->fields)) {
            foreach ($data as $key => $val) {
                if (!in_array($key, $this->fields, true)) {
                    unset($data[$key]);
                } elseif (is_scalar($val)) {
                    // 字段类型检查
                    $this->_parseType($data, $key);
                }
            }
        }
        // 安全过滤
        if (!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'], $data);
            unset($this->options['filter']);
        }
        $this->_before_write($data);
        return $data;
    }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data)
    {
    }

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data = '', $options = array(), $replace = false)
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = array();
            } else {
                $this->error = '_DATA_TYPE_INVALID_' ;
                return false;
            }
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 数据处理
        $data = $this->_facade($data);
        if (false === $this->_before_insert($data, $options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->db->insert($data, $options, $replace);
        if (false !== $result) {
            $insertId = $this->getLastInsID();
            if ($insertId) {
                // 自增主键返回插入ID
                $data[$this->getPk()] = $insertId;
                $this->_after_insert($data, $options);
                return $insertId;
            }
            $this->_after_insert($data, $options);
        }
        return $result;
    }

    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options)
    {
    }

    // 插入成功后的回调方法
    protected function _after_insert($data, $options)
    {
    }

    public function addAll($dataList, $options = array(), $replace = false)
    {
        if (empty($dataList)) {
            $this->error = '_DATA_TYPE_INVALID_' ;
            return false;
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 数据处理
        foreach ($dataList as $key => $data) {
            $dataList[$key] = $this->_facade($data);
        }
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList, $options, $replace);
        if (false !== $result) {
            $insertId = $this->getLastInsID();
            if ($insertId) {
                return $insertId;
            }
        }
        return $result;
    }

    /**
     * 通过Select方式添加记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields = '', $table = '', $options = array())
    {
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        if (false === $result = $this->db->selectInsert($fields ? $fields : $options['field'], $table ? $table : $this->getTableName(), $options)) {
            // 数据库插入操作失败
            $this->error = '_OPERATION_WRONG_' ;
            return false;
        } else {
            // 插入成功
            return $result;
        }
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data = '', $options = array())
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = array();
            } else {
                $this->error = '_DATA_TYPE_INVALID_' ;
                return false;
            }
        }
        // 数据处理
        $data = $this->_facade($data);
        // 分析表达式
        $options = $this->_parseOptions($options);
        $pk = $this->getPk();
        if (!isset($options['where'])) {
            // 如果存在主键数据 则自动作为更新条件
            if (isset($data[$pk])) {
                $where[$pk] = $data[$pk];
                $options['where'] = $where;
                unset($data[$pk]);
            } else {
                // 如果没有任何更新条件则不执行
                $this->error = '_OPERATION_WRONG_' ;
                return false;
            }
        }
        //禁用无条件更新
        if (empty($options['where'])) {
            return false;
        }
        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }
        if (false === $this->_before_update($data, $options)) {
            return false;
        }
        $result = $this->db->update($data, $options);
        if (false !== $result) {
            if (isset($pkValue)) $data[$pk] = $pkValue;
            $this->_after_update($data, $options);
        }
        return $result;
    }

    // 更新数据前的回调方法
    protected function _before_update(&$data, $options)
    {
    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options)
    {
    }

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options = array())
    {
        if (empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if (!empty($this->data) && isset($this->data[$this->getPk()]))
                return $this->delete($this->data[$this->getPk()]);
            else
                return false;
        }
        $pk = $this->getPk();
        if (is_numeric($options) || is_string($options)) {
            // 根据主键删除记录
            if (strpos($options, ',')) {
                $where[$pk] = array('IN', $options);
            } else {
                $where[$pk] = $options;
            }
            $options = array();
            $options['where'] = $where;
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        //禁用无条件删除
        if (empty($options['where'])) {
            return false;
        }
        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }
        $result = $this->db->delete($options);
        if (false !== $result) {
            $data = array();
            if (isset($pkValue)) $data[$pk] = $pkValue;
            $this->_after_delete($data, $options);
        }
        // 返回删除记录个数
        return $result;
    }

    // 删除成功后的回调方法
    protected function _after_delete($data, $options)
    {
    }

    /**
     * 查询数据集
     * @access public
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options = array())
    {
        if (is_string($options) || is_numeric($options)) {
            // 根据主键查询
            $pk = $this->getPk();
            if (strpos($options, ',')) {
                $where[$pk] = array('IN', $options);
            } else {
                $where[$pk] = $options;
            }
            $options = array();
            $options['where'] = $where;
        } elseif (false === $options) { // 用于子查询 不查询只返回SQL
            $options = array();
            // 分析表达式
            $options = $this->_parseOptions($options);
            return '( ' . $this->db->buildSelectSql($options) . ' )';
        }
        // 分析表达式
        $options   = $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) { // 查询结果为空
            return null;
        }
        $this->_after_select($resultSet, $options);
        return $resultSet;
    }

    // 查询成功后的回调方法
    protected function _after_select(&$resultSet, $options)
    {
    }

    /**
     * 生成查询SQL 可用于子查询
     * @access public
     * @param array $options 表达式参数
     * @return string
     */
    public function buildSql($options = array())
    {
        // 分析表达式
        $options = $this->_parseOptions($options);
        return '( ' . $this->db->buildSelectSql($options) . ' )';
    }

    /**
     * 分析表达式
     * @access protected
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options = array())
    {
        if (is_array($options))
            $options = array_merge($this->options, $options);
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options = array();
        if (!isset($options['table'])) {
            // 自动获取表名
            $options['table'] = $this->getTableName();
            $fields = $this->fields;
        } else {
            // 指定数据表 则重新获取字段列表 但不支持类型检测
            $fields = $this->getDbFields();
        }

        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 记录操作的模型名称
        $options['model'] = $this->name;

        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    }
                } elseif (!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos($key, '.') && false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')) {
                    unset($options['where'][$key]);
                }
            }
        }

        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }

    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {
    }

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
        if (empty($this->options['bind'][':' . $key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum')) {
                // 支持ENUM类型优先检测
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                //$data[$key]   =  intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key] = floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key] = (bool)$data[$key];
            }
        }
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options = array())
    {
        if (is_numeric($options) || is_string($options)) {
            $where[$this->getPk()] = $options;
            $options = array();
            $options['where'] = $where;
        }
        // 总是查找一条记录
        $options['limit'] = 1;
        // 分析表达式
        $options   = $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) {// 查询结果为空
            return null;
        }
        $this->data = $resultSet[0];
        $this->_after_find($this->data, $options);
        if (!empty($this->options['result'])) {
            return $this->returnResult($this->data, $this->options['result']);
        }
        return $this->data;
    }

    // 查询成功的回调方法
    protected function _after_find(&$result, $options)
    {
    }

    protected function returnResult($data, $type = '')
    {
        if ($type) {
            if (is_callable($type)) {
                return call_user_func($type, $data);
            }
            switch (strtolower($type)) {
                case 'json':
                    return json_encode($data);
                case 'xml':
                    return xml_encode($data);
            }
        }
        return $data;
    }

    /**
     * 处理字段映射
     * @access public
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    public function parseFieldsMap($data, $type = 1)
    {
        // 检查字段映射
        if (!empty($this->_map)) {
            foreach ($this->_map as $key => $val) {
                if ($type == 1) { // 读取
                    if (isset($data[$val])) {
                        $data[$key] = $data[$val];
                        unset($data[$val]);
                    }
                } else {
                    if (isset($data[$key])) {
                        $data[$val] = $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field 字段名
     * @param string $value 字段值
     * @return boolean
     */
    public function setField($field, $value = '')
    {
        if (is_array($field)) {
            $data = $field;
        } else {
            $data[$field] = $value;
        }
        return $this->save($data);
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field 字段名
     * @param integer $step 增长值
     * @return boolean
     */
    public function setInc($field, $step = 1)
    {
        return $this->setField($field, array('exp', $field . '+' . $step));
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field 字段名
     * @param integer $step 减少值
     * @return boolean
     */
    public function setDec($field, $step = 1)
    {
        return $this->setField($field, array('exp', $field . '-' . $step));
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field 字段名
     * @param string $sepa 字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options = $this->_parseOptions($options);
        $field   = trim($field);
        if (strpos($field, ',')) { // 多字段
            if (!isset($options['limit'])) {
                $options['limit'] = is_numeric($sepa) ? $sepa : '';
            }
            $resultSet = $this->db->select($options);
            if (!empty($resultSet)) {
                $_field = explode(',', $field);
                $field  = array_keys($resultSet[0]);
                $key    = array_shift($field);
                $key2   = array_shift($field);
                $cols   = array();
                $count  = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, $result) : $result;
                    }
                }
                return $cols;
            }
        } else {   // 查找一条记录
            // 返回数据个数
            if (true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit'] = is_numeric($sepa) ? $sepa : 1;
            }
            $result = $this->db->select($options);
            $array  = null;
            if (!empty($result)) {
                if (true !== $sepa && 1 == $options['limit']) return reset($result[0]);
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 创建数据对象 但不保存到数据库
     * @access public
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create($data = '', $type = '')
    {
        // 如果没有传值默认取POST数据
        if (empty($data)) {
            $data = $_POST;
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        // 验证数据
        if (empty($data) || !is_array($data)) {
            $this->error = '_DATA_TYPE_INVALID_' ;
            return false;
        }

        // 检查字段映射
        $data = $this->parseFieldsMap($data, 0);

        // 状态
        $type = $type ? $type : (!empty($data[$this->getPk()]) ? self::MODEL_UPDATE : self::MODEL_INSERT);

        // 检测提交字段的合法性
        if (isset($this->options['field'])) { // $this->field('field1,field2...')->create()
            $fields = $this->options['field'];
            unset($this->options['field']);
        } elseif ($type == self::MODEL_INSERT && isset($this->insertFields)) {
            $fields = $this->insertFields;
        } elseif ($type == self::MODEL_UPDATE && isset($this->updateFields)) {
            $fields = $this->updateFields;
        }
        if (isset($fields)) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            // 判断令牌验证字段
            if (C('TOKEN_ON')) $fields[] = C('TOKEN_NAME');
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }

        // 数据自动验证
        if (!$this->autoValidation($data, $type)) return false;

        // 表单令牌验证
        if (!$this->autoCheckToken($data)) {
            $this->error = '_TOKEN_ERROR_' ;
            return false;
        }

        // 验证完成生成数据对象
        if ($this->autoCheckFields) { // 开启字段检测 则过滤非法字段数据
            $fields = $this->getDbFields();
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                } elseif (MAGIC_QUOTES_GPC && is_string($val)) {
                    $data[$key] = stripslashes($val);
                }
            }
        }

        // 创建完成对数据进行自动处理
        $this->autoOperation($data, $type);
        // 赋值当前数据对象
        $this->data = $data;
        // 返回创建的数据以供其他调用
        return $data;
    }

    // 自动表单令牌验证
    // TODO  ajax无刷新多次提交暂不能满足
    public function autoCheckToken($data)
    {   //去掉，好像用不到
        // 支持使用token(false) 关闭令牌验证
        if (isset($this->options['token']) && !$this->options['token']) return true;
        if (C('TOKEN_ON')) {
            $name = C('TOKEN_NAME');
            if (!isset($data[$name]) || !isset($_SESSION[$name])) { // 令牌数据无效
                return false;
            }

            // 令牌验证
            list($key, $value) = explode('_', $data[$name]);
            if ($value && $_SESSION[$name][$key] === $value) { // 防止重复提交
                unset($_SESSION[$name][$key]); // 验证完成销毁session
                return true;
            }
            // 开启TOKEN重置
            if (C('TOKEN_RESET')) unset($_SESSION[$name][$key]);
            return false;
        }
        return true;
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value 要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
        $validate = array(
            'require' => '/.+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^\d{6}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
        );
        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)]))
            $rule = $validate[strtolower($rule)];
        return preg_match($rule, $value) === 1;
    }

    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    private function autoOperation(&$data, $type)
    {
        if (!empty($this->options['auto'])) {
            $_auto = $this->options['auto'];
            unset($this->options['auto']);
        } elseif (!empty($this->_auto)) {
            $_auto = $this->_auto;
        }
        // 自动填充
        if (isset($_auto)) {
            foreach ($_auto as $auto) {
                // 填充因子定义格式
                // array('field','填充内容','填充条件','附加规则',[额外参数])
                if (empty($auto[2])) $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
                if ($type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    switch (trim($auto[3])) {
                        case 'function':    //  使用函数进行填充 字段的值作为参数
                        case 'callback': // 使用回调方法
                            $args = isset($auto[4]) ? (array)$auto[4] : array();
                            if (isset($data[$auto[0]])) {
                                array_unshift($args, $data[$auto[0]]);
                            }
                            if ('function' == $auto[3]) {
                                $data[$auto[0]] = call_user_func_array($auto[1], $args);
                            } else {
                                $data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
                            }
                            break;
                        case 'field':    // 用其它字段的值进行填充
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case 'ignore': // 为空忽略
                            if ('' === $data[$auto[0]])
                                unset($data[$auto[0]]);
                            break;
                        case 'string':
                        default: // 默认作为字符串填充
                            $data[$auto[0]] = $auto[1];
                    }
                    if (false === $data[$auto[0]]) unset($data[$auto[0]]);
                }
            }
        }
        return $data;
    }

    /**
     * 自动表单验证
     * @access protected
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation($data, $type)
    {
        if (!empty($this->options['validate'])) {
            $_validate = $this->options['validate'];
            unset($this->options['validate']);
        } elseif (!empty($this->_validate)) {
            $_validate = $this->_validate;
        }
        // 属性验证
        if (isset($_validate)) { // 如果设置了数据自动验证则进行数据验证
            if ($this->patchValidate) { // 重置验证错误信息
                $this->error = array();
            }
            foreach ($_validate as $key => $val) {
                // 验证因子定义格式
                // array(field,rule,message,condition,type,when,params)
                // 判断是否需要执行验证
                if (empty($val[5]) || $val[5] == self::MODEL_BOTH || $val[5] == $type) {
                    if (0 == strpos($val[2], '{%') && strpos($val[2], '}'))
                        // 支持提示信息的多语言 使用 {%语言定义} 方式
                        $val[2] = L(substr($val[2], 2, -1));
                    $val[3] = isset($val[3]) ? $val[3] : self::EXISTS_VALIDATE;
                    $val[4] = isset($val[4]) ? $val[4] : 'regex';
                    // 判断验证条件
                    switch ($val[3]) {
                        case self::MUST_VALIDATE:   // 必须验证 不管表单是否有设置该字段
                            if (false === $this->_validationField($data, $val))
                                return false;
                            break;
                        case self::VALUE_VALIDATE:    // 值不为空的时候才验证
                            if ('' != trim($data[$val[0]]))
                                if (false === $this->_validationField($data, $val))
                                    return false;
                            break;
                        default:    // 默认表单存在该字段就验证
                            if (isset($data[$val[0]]))
                                if (false === $this->_validationField($data, $val))
                                    return false;
                    }
                }
            }
            // 批量验证的时候最后返回错误
            if (!empty($this->error)) return false;
        }
        return true;
    }

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val)
    {
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[$val[0]] = $val[2];
            } else {
                $this->error = $val[2];
                return false;
            }
        }
        return;
    }

    /**
     * 根据验证因子验证字段
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val)
    {
        switch (strtolower(trim($val[4]))) {
            case 'function':// 使用函数进行验证
            case 'callback':// 调用方法进行验证
                $args = isset($val[6]) ? (array)$val[6] : array();
                if (is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field)
                        $_data[$field] = $data[$field];
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                if ('function' == $val[4]) {
                    return call_user_func_array($val[1], $args);
                } else {
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // 验证某个值是否唯一
                if (is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                $map = array();
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field)
                        $map[$field] = $data[$field];
                } else {
                    $map[$val[0]] = $data[$val[0]];
                }
                if (!empty($data[$this->getPk()])) { // 完善编辑的时候验证唯一
                    $map[$this->getPk()] = array('neq', $data[$this->getPk()]);
                }
                if ($this->where($map)->find()) return false;
                return true;
            default:  // 检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex')
    {
        $type = strtolower(trim($type));
        switch ($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                } else {
                    list($min, $max) = explode(',', $rule);
                }
                return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length = mb_strlen($value, 'utf-8'); // 当前数据长度
                if (strpos($rule, ',')) { // 长度区间
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else {// 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start, $end) = explode(',', $rule);
                if (!is_numeric($start)) $start = strtotime($start);
                if (!is_numeric($end)) $end = strtotime($end);
                return NOW_TIME >= $start && NOW_TIME <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array(get_client_ip(), explode(',', $rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(get_client_ip(), explode(',', $rule));
            case 'regex':
            default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value, $rule);
        }
    }

    /**
     * SQL查询
     * @access public
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return mixed
     */
    public function query($sql, $parse = false)
    {
        if (!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql = $this->parseSql($sql, $parse);
        return $this->db->query($sql);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return false | integer
     */
    public function execute($sql, $parse = false)
    {
        if (!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql = $this->parseSql($sql, $parse);
        return $this->db->execute($sql);
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql SQL指令
     * @param boolean $parse 是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql, $parse)
    {
        // 分析表达式
        if (true === $parse) {
            $options = $this->_parseOptions();
            $sql     = $this->db->parseSql($sql, $options);
        } elseif (is_array($parse)) { // SQL预处理
            $parse = array_map(array($this->db, 'escapeString'), $parse);
            $sql   = vsprintf($sql, $parse);
        } else {
            $sql = strtr($sql, array('__TABLE__' => $this->getTableName(), '__PREFIX__' =>  DB_PREFIX));
        }
        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     * 切换当前的数据库连接
     * @access public
     * @param integer $linkNum 连接序号
     * @param mixed $config 数据库连接信息
     * @param array $params 模型参数
     * @return Model
     */
    public function db($linkNum = 0, $config = '', $params = array())
    {
        if ('' === $linkNum && $this->db) {
            return $this->db;
        }
        if (!isset($this->_db[$linkNum]) || (isset($this->_db[$linkNum]) && $config && $this->_linkNum[$linkNum] != $config)) {
            // 创建一个新的实例
            if (!empty($config) && is_string($config) && false === strpos($config, '/')) { // 支持读取配置参数
                $config = C($config);//去掉或改造
            }
            $this->_db[$linkNum] = Db::getInstance($config);
        } elseif (NULL === $config) {
            $this->_db[$linkNum]->close(); // 关闭数据库连接
            unset($this->_db[$linkNum]);
            return null;
        }
        if (!empty($params)) {
            if (is_string($params)) parse_str($params, $params);
            foreach ($params as $name => $value) {
                $this->setProperty($name, $value);
            }
        }
        // 记录连接信息

        $this->_linkNum[$linkNum] = $config;
        // 切换数据库连接
        $this->db = $this->_db[$linkNum];
        $this->_after_db();
        // 字段检测
        if (!empty($this->name) && $this->autoCheckFields) $this->_checkTableInfo();
        return $this;
    }

    // 数据库切换后回调方法
    protected function _after_db()
    {
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName()
    {
        if (empty($this->name))
            $this->name = substr(get_class($this), 0, -5);
        return $this->name;
    }

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->trueTableName)) {
            $tableName = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if (!empty($this->tableName)) {
                $tableName .= $this->tableName;
            } else {
                $tableName .= parse_name($this->name);
            }
            $this->trueTableName = strtolower($tableName);
        }
        return (!empty($this->dbName) ? $this->dbName . '.' : '') . $this->trueTableName;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        $this->commit();
        $this->db->startTrans();
        return;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        return $this->db->rollback();
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 返回数据库的错误信息
     * @access public
     * @return string
     */
    public function getDbError()
    {
        return $this->db->getError();
    }

    /**
     * 返回最后插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID()
    {
        return $this->db->getLastInsID();
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->db->getLastSql($this->name);
    }

    // 鉴于getLastSql比较常用 增加_sql 别名
    public function _sql()
    {
        return $this->getLastSql();
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk()
    {
        return isset($this->fields['_pk']) ? $this->fields['_pk'] : $this->pk;
    }

    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields()
    {
        if (isset($this->options['table'])) {// 动态指定表名
            $fields = $this->db->getFields($this->options['table']);
            return $fields ? array_keys($fields) : false;
        }
        if ($this->fields) {
            $fields = $this->fields;
            unset($fields['_autoinc'], $fields['_pk'], $fields['_type'], $fields['_version']);
            return $fields;
        }
        return false;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data = '')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        } elseif (is_string($data)) {
            parse_str($data, $data);
        } elseif (!is_array($data)) {
            print_x( '_DATA_TYPE_INVALID_' );
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @return Model
     */
    public function join($join)
    {
        if (is_array($join)) {
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            $this->options['join'][] = $join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     * @param mixed $union
     * @param boolean $all
     * @return Model
     */
    public function union($union, $all = false)
    {
        if (empty($union)) return $this;
        if ($all) {
            $this->options['union']['_all'] = true;
        }
        if (is_object($union)) {
            $union = get_object_vars($union);
        }
        // 转换union表达式
        if (is_string($union)) {
            $options = $union;
        } elseif (is_array($union)) {
            if (isset($union[0])) {
                $this->options['union'] = array_merge($this->options['union'], $union);
                return $this;
            } else {
                $options = $union;
            }
        } else {
            print_x( '_DATA_TYPE_INVALID_' );
            $options = null;
        }
        $this->options['union'][] = $options;
        return $this;
    }

    /**
     * 查询缓存
     * @access public
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return Model
     */
    public function cache($key = true, $expire = null, $type = '')
    {
        if (false !== $key)
            $this->options['XCache'] = array('key' => $key, 'expire' => $expire, 'type' => $type);
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field, $except = false)
    {
        if (true === $field) {// 获取全部字段
            $fields = $this->getDbFields();
            $field = $fields ? $fields : '*';
        } elseif ($except) {// 字段排除
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getDbFields();
            $field = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope = '', $args = NULL)
    {
        if ('' === $scope) {
            if (isset($this->_scope['default'])) {
                // 默认的命名范围
                $options = $this->_scope['default'];
            } else {
                return $this;
            }
        } elseif (is_string($scope)) { // 支持多个命名范围调用 用逗号分割
            $scopes = explode(',', $scope);
            $options = array();
            foreach ($scopes as $name) {
                if (!isset($this->_scope[$name])) continue;
                $options = array_merge($options, $this->_scope[$name]);
            }
            if (!empty($args) && is_array($args)) {
                $options = array_merge($options, $args);
            }
        } elseif (is_array($scope)) { // 直接传入命名范围定义
            $options = $scope;
        } else {
            $options = null;
        }

        if (is_array($options) && !empty($options)) {
            $this->options = array_merge($this->options, array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return Model
     */
    public function where($where, $parse = null)
    {
        if (!is_null($parse) && is_string($where)) {
            if (!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map(array($this->db, 'escapeString'), $parse);
            $where = vsprintf($where, $parse);
        } elseif (is_object($where)) {
            $where = get_object_vars($where);
        }
        if (is_string($where) && '' != $where) {
            $map = array();
            $map['_string'] = $where;
            $where = $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }

        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset, $length = null)
    {
        $this->options['limit'] = is_null($length) ? $offset : $offset . ',' . $length;
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public function page($page, $listRows = null)
    {
        $this->options['page'] = is_null($listRows) ? $page : $page . ',' . $listRows;
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment)
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * 设置模型的属性值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return Model
     */
    public function setProperty($name, $value)
    {
        if (property_exists($this, $name))
            $this->$name = $value;
        return $this;
    }

}
?>