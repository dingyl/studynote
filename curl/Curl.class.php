<?php
class Curl{
    protected $url;
    protected $curl;

    public function __construct($url=''){
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $this->setUrl($url);
    }

    /**
     * 设置url
     * @param $url
     * @return $this
     */
    public function setUrl($url){
        $this->url = $url;
        curl_setopt($this->curl,CURLOPT_URL, $this->url);
        return $this;
    }

    /**
     * 设置header请求头
     * @param $header
     * @return $this
     */
    public function setHeader($header){
        curl_setopt($this->curl, CURLOPT_HEADER, $header);
        return $this;
    }

    /**
     * 请求中添加user-agent头的字符串。
     * @param $agent
     * @return $this
     */
    public function setUserAgent($agent){
        curl_setopt($this->curl,CURLOPT_USERAGENT,$agent);
        return $this;
    }

    /**
     * 设置请求来源地址
     */
    public function setRefererUrl($url){
        curl_setopt($this->curl,CURLOPT_REFERER,$url);
        return $this;
    }

    /**
     * 设置cookie头信息
     * @param $cookie
     * @return $this
     */
    public function setCookie($cookies){
        $cookie = [] ;
        if(is_array($cookies)){
            foreach ($cookies as $name=>$value){
                $cookie[] = $name."=".$value;
            }
            $cookies = implode(';',$cookie);
        }

        curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);
        return $this;
    }

    /**
     * 返回所有的链接数组
     */
    public function getLinks(){
        $regex = "/<a href=\"([^\'\"]*)\" [^>]*>(.*?)<\/a>/";
        preg_match_all($regex,$this->getContent(),$matchs);
        $links = [];
        foreach($matchs[1] as $k=>$href){
            //去除掉空连接
            if(substr($href,0,12)!="javascript:;"){
                //补全链接地址
                if(substr($href,0,4)!="http"){
                    $href=$this->url.$href;
                }
                array_push($links,$href);
            }
        }
        return $links;
    }

    /**
     * get请求
     * @return mixed
     */
    public function get($data){
        $this->setData($data);
        return $this->getContent();
    }

    /**
     * post请求
     * @param $data
     * @return mixed
     */
    public function post($data){
        $this->setPost(1);
        $this->setData($data);
        return $this->getContent();
    }


    /**
     * 单文件上传,默认根据表单上传
     * @param $field
     * @param string $filepath
     * @return mixed
     */
    public function uploadFile($field,$filepath=""){
        $data = [];
        if(empty($filepath)){
            //有文件上传
            if(isset($_FILES[$field])){
                //多文件情况
                if(is_array($_FILES[$field]['name'])){
                    $count = $_FILES[$field]['name'];
                    foreach($_FILES[$field]['error'] as $k=>$v){
                        //上传文件没有出错
                        $v==0 && $data[$field.$k]='@'.$_FILES[$field]['tmp_name'][$k];
                    }
                }else{//单文件情况
                    $_FILES[$field]['error'] == 0 && $data[$field]='@'.$_FILES[$field]['tmp_name'];
                }
            }
        }else{
            $data = [$field=>'@'.$filepath];
        }
        $this->filter($data);
        return $this->post($data);
    }

    /**
     * 下载文件
     * @param $url
     * @param $save_path
     */
    public function downFile($url,$save_path){
        $ourl = $this->url;
        $this->setUrl($url);
        $this->setTimeOut(0);
        $filecontent = $this->getContent();
        file_put_contents($save_path,$filecontent);
        $this->setUrl($ourl);
    }


    /**
     * 设置超时时间
     * @param $time
     * @return $this
     */
    public function setTimeOut($time=30){
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $time);
        return $this;
    }

    public function getContent(){
        return curl_exec($this->curl);
    }

    public function close(){
        return curl_close($this->curl);
    }

    public function __destruct(){
        $this->close();
    }

    protected function filter(&$data){
        if(version_compare(phpversion(),"5.5") > 0){
            foreach ($data as $name=>$path){
                $data[$name] = new CURLFile(ltrim($path,'@'));
            }
        }else{
            curl_setopt($this->curl,CURLOPT_SAFE_UPLOAD, false);
        }
        return $this;
    }

    /**
     * 设置请求方式 post 默认为1 开启post，或者为get
     */
    protected function setPost($status=1){
        return curl_setopt($this->curl,CURLOPT_POST, 1);
    }

    protected function setData($data){
        return curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    }
}