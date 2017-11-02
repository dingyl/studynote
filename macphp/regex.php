<?php
/**
 * Class Validate验证类
 */
class Validate{
    static function is_email($email){
        return preg_match("/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/",$email);
    }
    static function is_phone($phone){
        return preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$phone);
    }
    /**
     * 返回密码级别,越大密码复杂度越高
     */
    static function passwdlevel($pwd){
        $power = 0;
        $power+=(strlen($pwd)>=8);
        $power+=preg_match("/[a-z]/",$pwd);
        $power+=preg_match("/[A-Z]/",$pwd);
        $power+=preg_match("/\d/",$pwd);
        $power+=preg_match("/[~!@#$%^&*()_+]/",$pwd);
        return $power;
    }
}
