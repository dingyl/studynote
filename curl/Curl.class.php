<?php

class Curl
{
    protected $url;
    protected $curl;
    protected $temp_dir;

    public function __construct($url = '')
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $this->setUrl($url);
    }


    /**
     * 设置文件缓存目录
     * @param $path
     * @return $this
     */
    public function setTempDir($path){
        $this->temp_dir = $path;
        return $this;
    }

    /**
     * 设置url
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        return $this;
    }

    /**
     * 设置header请求头
     * @param $header
     * @return $this
     */
    public function setHeader($header)
    {
        curl_setopt($this->curl, CURLOPT_HEADER, $header);
        return $this;
    }

    /**
     * 请求中添加user-agent头的字符串。
     * @param $agent
     * @return $this
     */
    public function setUserAgent($agent)
    {
        curl_setopt($this->curl, CURLOPT_USERAGENT, $agent);
        return $this;
    }

    /**
     * 设置请求来源地址
     * @param $url
     * @return $this
     */
    public function setRefererUrl($url)
    {
        curl_setopt($this->curl, CURLOPT_REFERER, $url);
        return $this;
    }

    /**
     * 设置cookie头信息
     * @param $cookies
     * @return $this
     */
    public function setCookie($cookies)
    {
        $cookie = [];
        if (is_array($cookies)) {
            foreach ($cookies as $name => $value) {
                $cookie[] = $name . "=" . $value;
            }
            $cookies = implode(';', $cookie);
        }

        curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);
        return $this;
    }

    /**
     * 返回所有的链接数组
     * @return array
     */
    public function getLinks()
    {
        $regex = "/<a href=\"([^\'\"]*)\" [^>]*>(.*?)<\/a>/";
        preg_match_all($regex, $this->getContent(), $matchs);
        $links = [];
        foreach ($matchs[1] as $k => $href) {
            //去除掉空连接
            if (substr($href, 0, 12) != "javascript:;") {
                //补全链接地址
                if (substr($href, 0, 4) != "http") {
                    $href = $this->url . $href;
                }
                array_push($links, $href);
            }
        }
        return $links;
    }

    /**
     * get请求
     * @return mixed
     */
    public function get($data)
    {
        $url = $this->url.'?'.http_build_query($data);
        $this->setUrl($url);
        return $this->getContent();
    }

    /**
     * post请求  文件img=>@file_path
     * @param $data
     * @return mixed
     */
    public function post($data)
    {
        $this->setPost();
        $this->format($data);
        $this->setData($data);
        return $this->getContent();
    }


    /**
     * 转发请求
     * @return mixed
     */
    public function transferQuery(){
        if($this->isGet()){
            return $this->get($_GET);
        }

        if($this->isPost()){
            $post_data = $_POST;
            if($data = $this->isUpFile()){
                $post_data = array_merge($post_data,$data);
            }
            return $this->post($post_data);
        }
    }


    /**
     * 下载文件
     * @param $url
     * @param $save_path
     */
    public function downFile($url, $save_path)
    {
        $ourl = $this->url;
        $this->setUrl($url);
        $this->setTimeOut(0);
        $filecontent = $this->getContent();
        file_put_contents($save_path, $filecontent);
        $this->setUrl($ourl);
    }


    /**
     * 设置超时时间
     * @param $time
     * @return $this
     */
    public function setTimeOut($time = 30)
    {
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $time);
        return $this;
    }

    public function getContent()
    {
        return curl_exec($this->curl);
    }

    public function close()
    {
        return curl_close($this->curl);
    }

    public function __destruct()
    {
        $this->close();
    }


    /**
     * 格式化请求数据，使符合要求，支持数组和单、多文件
     * @param $data
     * @return $this
     */
    protected function format(&$data)
    {
        $is_gt = version_compare(phpversion(), "5.5");

        $is_gt || curl_setopt($this->curl, CURLOPT_SAFE_UPLOAD, false);

        foreach ($data as $field => $value) {
            if (is_string($value) && $value[0] == '@' && $is_gt) {
                $data[$field] = new CURLFile(ltrim($value, '@'));
            }

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($v[0] == '@' && $is_gt) {
                        $data[$field . "[$k]"] = new CURLFile(ltrim($v, '@'));
                    } else {
                        $data[$field . "[$k]"] = $v;
                    }
                }
                unset($data[$field]);
            }
        }
        return $this;
    }

    /**
     * 设置请求方式 post 默认为1 开启post，或者为get
     */
    private function setPost()
    {
        return curl_setopt($this->curl, CURLOPT_POST, 1);
    }

    private function setData($data)
    {
        return curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    }

    private function isGet(){
        return $this->server('REQUEST_METHOD') == 'GET';
    }

    private function isPost(){
        return $this->server('REQUEST_METHOD') == 'POST';
    }

    private function server($name){
        return $_SERVER[$name];
    }

    private function isUpFile(){
        $files = $_FILES;
        $data = [];
        if(count($files)){
            foreach($files as $field=>$file){
                if(is_array($file['error'])){
                    foreach($file['error'] as $k=>$error_code){
                        if($error_code == 0){
                            $file_name = $file['name'][$k];
                            $cache_file = $file['tmp_name'][$k];
                            $temp_file = $this->temp_dir.'/'.$file_name;
                            copy($cache_file,$temp_file);
                            $data[$field][$k] = "@".$temp_file;
                        }
                    }
                }

                //单文件
                if($file['error'] == 0){
                    $file_name = $file['name'];
                    $cache_file = $file['tmp_name'];
                    $temp_file = $this->temp_dir.'/'.$file_name;
                    copy($cache_file,$temp_file);
                    $data[$field] = "@".$temp_file;
                }
            }
        }
        if($data){
            return $data;
        }
        return false;
    }
}