<?php
//数组打印函数
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}


//日志打印函数
function put_log($msg,$file='./put.log'){
    $msg = var_export($msg,true);
    $visit_ip = $_SERVER["REMOTE_ADDR"];
    $visit_time = date("Y-m-d H:i:s",time());
    error_log($visit_ip.' '.$visit_time." : $msg\r\n",3,$file);
}



//魔术引号开启
//如果 magic_quotes_gpc 为关闭时返回 0，否则返回 1。在 PHP 5.4.O 起将始终返回 FALSE。
//放入数据库之前要用\引用引号
function _addslashes(&$arr){
    foreach ($arr as $k=>$v){
        if(is_array($v)){
            $arr[$k] = _addslashes($v);
        }else{
            $arr[$k] = addslashes($v);
        }
    }
}

//魔术引号过滤
function init_magic_quotes(){
    if(!get_magic_quotes_gpc()){
        _addslashes($_REQUEST);
        _addslashes($_GET);
        _addslashes($_POST);
    }
}
//思路:在进行数据操作的时候,才对个别数据进行引号过滤.或者采用pdo预处理查询方式
//sql拼接执行会造成sql注入.主要在where条件和update,insert上出现问题,可对db库进行封装处理
//防sql注入可以采用mysql的预处理特性,将参数和处理语句分开传送,避免sql拼接时被恶意输入改变元sql语义

//$preparedStatement = $db->prepare('INSERT INTO table (column) VALUES (:column)');
//$preparedStatement->execute(array(':column' => $unsafeValue));
//init_magic_quotes();
//p($_GET);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
</head>
<body>

</body>
</html>
