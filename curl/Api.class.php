<?php
include("../functions.php");
include("Curl.class.php");
set_time_limit(0);
error_reporting(0);
ini_set('memory_limit', '512M');
//接口测试类
class Api{
    protected $domain = "http://preach.com/api/";
    protected $image = '@/Users/apple/Downloads/59cb3a588efb7.png';
    protected $video = '@/Users/apple/Downloads/audio.mp3';
    protected $course_id;
    protected $chapter_id;
    protected $course_ware_id;
    protected $curl ;
    protected $reflect;
    protected $allowControllerIds = [
        'sale_zones',
        'soft_versions',
        'banners',
        'categories',
        'productChannels',
        'provinces',
        'cities',
        'courses',
        'chapters',
        'devices',
        'messages',
        'push',
        'teachers',
        'course_wares',
        'users'
    ];
    protected $umaskMethods = [
        '__construct',
        'exec',
        'users_logout',
        'users_login',
    ];

    public function __construct()
    {
        $this->curl = new Curl();
        $this->reflect = new ReflectionClass(get_called_class());
    }

    protected $common = [
        'code' => "tret",
        'debug' => '1',
        'dno' => '7e23ec3d1df294e5098098ea00ca6b59ce',
        'imei' => '868510026711191',
        'mobile' => '13912345678',
        'sid' => '6sbb989552a92f23df147bebea9522e3e1a8',
        'password' => '1234'
    ];

    protected $basic = [
        'code' => "tret",
        'debug' => '1',
    ];


    protected function basicPost($data,$url){
        return $this->basicRequest($data,$url,'basic','post');
    }

    protected function post($data,$url){
        return $this->basicRequest($data,$url,'common','post');
    }

    protected function get($data,$url){
        return $this->basicRequest($data,$url,'common','get');
    }

    protected function basicGet($data,$url){
        return $this->basicRequest($data,$url,'basic','get');
    }

    protected function setSid($data){
        isset($data['sid']) && $this->common['sid'] = $data['sid'];
    }

    protected function setCourseId($id){
        $this->course_id = $id;
    }

    protected function setChapterId($id){
        $this->chapter_id = $id;
    }

    protected function setCourseWareId($id){
        $this->course_ware_id = $id;
    }

    protected function basicRequest($data,$url,$type='basic',$method='get'){
        $this->curl->setUrl($this->domain.$url);
        @$resp_json = $this->curl->$method(array_merge($data,$this->$type));
        $resp_data = json_decode($resp_json,true);
        $this->formatPrint($resp_json,$data,$url,$method);
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