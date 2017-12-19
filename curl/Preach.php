<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
    <style>
        .main{
            border:1px solid #CCCCCC;
            padding:10px;
            box-shadow: #CCCCCC;
        }

        .interclass{
            color:orange
        }

        .success{
            color: green;
        }

        .fail{
            color: red;
        }
    </style>
</head>
<body>

<?php
include("../functions.php");
include("Curl.class.php");
set_time_limit(0);
error_reporting(0);
//接口测试类
class Preach{
    private $domain = "http://preach.com/api/";
    private $image = '@/Users/apple/Downloads/59cb3a588efb7.png';
    private $video = '@/Users/apple/Downloads/audio.mp3';
    private $curl ;
    private $reflect;
    private $umaskMethods = [
        '__construct',
        'exec',
        'post',
        'active',
        'setSid',
        'logout'
    ];

    public function __construct()
    {
        $this->curl = new Curl();
        $this->reflect = new ReflectionClass(__CLASS__);
    }

    private $common = [
        'code' => "tret",
        'debug' => '1',
        'dno' => '7e23ec3d1df294e5098098ea00ca6b59ce',
        'imei' => '868510026711191',
        'mobile' => '13912345678',
        'sid' => '7dd763ad188553c9db01e65c85bb7bef8ed3',
        'password' => '1234'
    ];

    private function post($data,$url){
        $this->interface = $url;
        $this->curl->setUrl($this->domain.$this->interface);
        @$resp_json = $this->curl->post(array_merge($data,$this->common));
        $resp_data = json_decode($resp_json,true);
        if($resp_data['error_code'] === 0){
            echo "<pre class='main'><span class='interclass'>$url</span> <strong class='success'>successful</strong> ! post data:(".json_encode($data).")response data:($resp_json)</pre>";
        }else{
            echo "<pre class='main'><span class='interclass'>$url</span> <strong class='fail'>fail</strong> ! post data:(".json_encode($data,true).")response data:($resp_json)</pre>";
        }
        return $resp_data;
    }

    private function setSid($data){
        isset($data['sid']) && $this->common['sid'] = $data['sid'];
    }

    //设备接口

    //设备激活
    public function active($data=['debug'=>'1','code'=>'tret'],$url='devices/active'){
        $data = json_decode($this->curl->post($data));
        $this->setSid($data);
    }

    //版本升级信息获取
    public function upgrade($data=[],$url='soft_versions/upgrade'){
        $this->post($data,$url);
    }


    //获取省份接口
    public function provinces($data=[],$url='provinces'){
        $this->post($data,$url);
    }


    //获取城市接口
    public function cities($data=[],$url='cities'){
        $this->post($data,$url);
    }

    //用户接口
    //登录
    public function login($data=[],$url='users/login'){
        $data = $this->post($data,$url);
        $this->setSid($data);
    }

    //关于我们
    public function about($data=[],$url='product_channels/about'){
        $this->post($data,$url);
    }

    //请求验证码
    public function sendAuth($data=[],$url='users/send_auth'){
        $this->post($data,$url);
    }

    public function logout($data=[],$url='users/logout'){
        $this->post($data,$url);
    }

    public function userDetail($data=[],$url='users/detail'){
        $this->post($data,$url);
    }


    //老师课程接口
    public function teacherCourseDetail($url='teachers/course_detail'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //新增课程接口
    public function teacherCourseCreate($url='teachers/course_create'){
//        $data = [
//            'id' => 1
//        ];
//        $this->post($data,$url);
    }


    public function teacherCourseUpdate($url='teachers/course_update'){
        $data = [
            'description'=>time(),
            'price' => 100,
            'id' => 1,
            'description_image_num'=>3,
            'surface_image' => $this->image,
            'description_image_0' => $this->image,
            'description_image_1' => $this->image,
            'description_image_2' => $this->image,
            'video_image' => $this->image
        ];
        $this->post($data,$url);
    }


    //章节列表接口
    public function teachersChapters($url='teachers/chapters'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }

    //章节详情接口
    public function teachersChapterDetail($url='teachers/chapter_detail'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //新增章节详情接口
    public function teachersChapterCreate($url='teachers/chapter_create'){
//        $data = [
//            'id' => 1
//        ];
//        $this->post($data,$url);
    }

    //更新章节详情接口
    public function teachersChapterUpdate($url='teachers/chapter_update'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //删除课程介绍图片
    public function teachersCourseImageDelete($url='teachers/course_image_delete'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }


    //课件列表接口
    public function courseWares($url='course_wares'){
        $data = [
            'chapter_id' => 1
        ];
        $this->post($data,$url);
    }

    //新增课件接口
    public function courseWaresCreate($url='course_wares/create'){
        $data = [
            'chapter_id' => 1,
            'image' => $this->image,
            'audio' => $this->video
        ];
        $this->post($data,$url);
    }

    //更新课件接口
    public function courseWaresUpdate($url='course_wares/update'){
        $data = [
            'id' => 1,
            'image' => $this->image,
            'audio' => $this->video
        ];
        $this->post($data,$url);
    }

    //删除课件接口
    public function courseWaresDelete($url='course_wares/delete'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //排序列表接口
    public function courseWaresSort($url='course_wares/sort'){
        $data = [
            'ids' => [
                23,24,46
            ]
        ];
        $this->post($data,$url);
    }

    //课件详情接口
    public function courseWaresDetail($url='course_wares/detail'){
        $data = [
            'id	' => 1
        ];
        $this->post($data,$url);
    }

    //我的提问列表
    public function messagesUser($url='messages/user'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }

    //房间的问题列表
    public function messagesRoom($url='messages/room'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }


    //提问接口
    public function messagesMail($url='messages/mail'){
        $data = [
            'chapter_id' => 1,
            'content' => '提问测试信息'
        ];
        $this->post($data,$url);
    }


    //老师的课程问题列表(老师入口)
    public function messagesCourse($url='messages/course'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }

    //回复问题接口(老师入口)
    public function messagesReply($url='messages/reply'){
        $data = [
            'first_message_id' => 1,
            'content' => '回复测试信息'
        ];
        $this->post($data,$url);
    }


    //获取Banners接口
    public function banners($url='banners'){
        $this->post([],$url);
    }


    //点击Banner上报接口
    public function bannersClick($url='banners/click'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }



    //获取分类接口
    public function categories($url='categories'){
        $this->post([],$url);
    }



    //获取一级分类接口
    public function categoriesFirst($url='categories/first'){
        $this->post([],$url);
    }


    //活动专区列表接口
    public function sale_zones($url='sale_zones'){
        $this->post([],$url);
    }

    //课程列表接口
    public function coursesSearch($url='courses/search'){
        $this->post([],$url);
    }

    //热门推荐接口
    public function coursesHot($url='courses/hot'){
        $this->post([],$url);
    }

    //课程详情接口
    public function coursesDetail($url='courses/detail'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //课程章节列表接口
    public function chapters($url='chapters'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }


    //章节详情接口
    public function chaptersDetail($url='chapters/detail'){
        $data = [
            'course_id' => 1
        ];
        $this->post($data,$url);
    }


    //房间接口
    public function chaptersRoom($url='chapters/room'){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //播放上报接口
    public function chaptersPlay($url='chapters/play'){
        $data = [
            'course_ware_id' => 1
        ];
        $this->post($data,$url);
    }

    public function exec(){
        $methods = $this->reflect->getMethods();
        foreach($methods as $method){
            if(!in_array($method->name,$this->umaskMethods)){
                call_user_func([$method->class,$method->name]);
            }
        }
    }

}

$preach = new Preach();
//$preach->exec();
$str = "hello_world_bye";
function convert($str){
    $str = strtolower($str);
    $arr = explode('_',$str);
    foreach($arr as $k=>$v){
        $arr[$k] = strtoupper($v[0]).substr($v,1);
    }
    return implode('',$arr);
}

echo convert($str);


?>
</body>
</html>