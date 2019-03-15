<?php

namespace libs;

/**
 * 跳转工具类
 * Class Url
 * @package libs
 */
class Url
{
    public static function redirect($uri)
    {
        header('Location: ' . $uri);
    }
}