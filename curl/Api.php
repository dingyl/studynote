<?php
include("../functions.php");
include("Curl.php");
set_time_limit(0);
error_reporting(0);
ini_set('memory_limit', '512M');
//接口测试类
class Api{
    protected $domain = "http://preach.com/api/";
    protected $image = '@/Users/apple/Downloads/59cb3a588efb7.png';
    protected $video = '@/Users/apple/Downloads/audio.mp3';
    protected $curl ;
    protected $reflect;
    protected $allowControllerIds = [
    ];
    protected $umaskMethods = [
        '__construct',
        'exec'
    ];

    public function __construct()
    {
        $this->curl = new Curl();
        $this->reflect = new ReflectionClass(get_called_class());
    }

    protected function post($data,$url){
        return $this->request($data,$url,'post');
    }

    protected function get($data,$url){
        return $this->request($data,$url,'get');
    }

    protected function request($data,$url,$type){
        $this->curl->setUrl($this->domain.$url);
        @$resp_json = $this->curl->$type($data);
        $resp_data = json_decode($resp_json,true);
        $this->formatPrint($resp_json,$data,$url,$type);
        return $resp_data;
    }

    /**
     * 格式化输出信息
     * @param $resp_json
     * @param $data
     * @param $url
     */
    protected function formatPrint($resp_json,$data,$url,$method){
        $resp_data = json_decode($resp_json,true);
        $str = "<pre class='main wrapfail'><span class='interclass'>$url</span> <strong class='fail'>fail</strong> ! ".$method." data:(".json_encode($data).")response data:(".$resp_json.")</pre>";
        if($resp_data['error_code'] === 0){
            $str = str_replace('fail','success',$str);
        }
        echo $str;
    }

    /**
     * 下划线转驼峰方法
     * @param $underline
     * @return mixed
     */
    protected function underLineToCamel($underline){
        return str_replace(' ','',ucwords(implode(' ',explode('_',$underline))));
    }


    /**
     * 驼峰转下划线方法
     * @param $camel
     * @return string
     */
    protected function camelToUnderLine($camel){
        return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $camel));
    }


    protected function call($function_name){
        $temp = explode('_',$function_name);
        $controller_id = $this->camelToUnderLine($temp[0]);
        if(in_array($controller_id,$this->allowControllerIds)){
            if(isset($temp[1])){
                $action_id = $this->camelToUnderLine($temp[1]);
                $url = $controller_id.'/'.$action_id;
            }else{
                $url = $controller_id;
            }
            call_user_func([get_called_class(),$function_name],$url);
        }
    }

    public function exec(){
        $methods = $this->reflect->getMethods();
        foreach($methods as $method){
            if(!in_array($method->name,$this->umaskMethods) && $method->isPublic()){
                $this->call($method->name);
            }
        }
    }
}