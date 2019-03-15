<?php

namespace controllers;

use libs\Url;

class BaseController
{
    /**
     * @var array 模板参数
     */
    protected $view = [];

    /**
     * @var 控制器名称
     */
    public $id;

    /**
     * @var 控制器方法名称
     */
    public $action;

    /**
     * 判断是否是get请求
     * @return bool
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;
    }

    /**
     * 判断是否是post请求
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;
    }

    /**
     * 跳转
     * @param $url
     */
    protected function redirect($url)
    {
        Url::redirect($url);
    }

    protected function renderJson($error_code, $error_reason, $data = [])
    {
        $resp = [
            'error_code' => $error_code,
            'error_reason' => $error_reason,
        ];
        return array_merge($resp, $data);
    }

    /**
     * 渲染模板
     * @return false|string
     */
    protected function renderView()
    {
        ob_start();
        extract($this->view);
        $file_path = VIEWS_DIR . '/' . $this->id . '/' . $this->action . '.php';
        if (file_exists($file_path)) {
            require $file_path;
        }
        $temp_html = ob_get_contents();
        ob_clean();
        return $temp_html;
    }


    public function run($id)
    {
        $action = $this->action;
        $result = $this->$action($id);
        if (!empty($result)) {
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            echo $this->renderView();
        }
    }
}