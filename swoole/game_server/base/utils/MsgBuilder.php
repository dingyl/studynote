<?php

namespace base\utils;

class MsgBuilder
{
    public static function buildMsg($action, $data)
    {
        return [
            'action' => $action,
            'data' => $data
        ];
    }

    public static function buildJson($action, $data)
    {
        $msg = static::buildMsg($action, $data);
        return json_encode($msg, JSON_UNESCAPED_UNICODE);
    }

    public static function buildError($error_code_map,$error_code)
    {
        return [
            'error_code' => $error_code,
            'error_reason' => $error_code_map[$error_code]
        ];
    }
}