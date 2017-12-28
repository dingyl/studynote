<title>接口测试</title>
<style>
    .main{
        padding:10px;
        font-size: 16px;
    }

    .wrapfail{
        border: 2px solid red;
    }

    .wrapsuccess{
        border:2px solid green;
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

<?php
include("../functions.php");
include("Curl.php");
set_time_limit(0);
error_reporting(0);
//ini_set('memory_limit', '512M');
//接口测试类
class Preach{
    private $domain = "http://preach.com/api/";
    private $image = '@/Users/apple/Downloads/59cb3a588efb7.png';
    private $video = '@/Users/apple/Downloads/audio.mp3';
    private $audio = '@/Users/apple/Downloads/yinyue.mp3';
    private $course_id;
    private $chapter_id;
    private $course_ware_id;
    private $curl ;
    private $reflect;
    private $allowControllerIds = [
//        'sale_zones',
//        'soft_versions',
//        'banners',
//        'categories',
//        'productChannels',
//        'provinces',
//        'cities',

//        'courses',
//        'chapters',

//        'devices',

//        'messages',

//        'push',

//        'teachers',
        'course_wares',

//        'users'
    ];
    private $umaskMethods = [
        '__construct',
        'exec',
        'users_logout',
        'users_login',
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
        'sid' => '6sbb989552a92f23df147bebea9522e3e1a8',
        'password' => '1234'
    ];

    private $basic = [
        'code' => "tret",
        'debug' => '1',
    ];


    private function basicPost($data,$url){
        return $this->basicRequest($data,$url,'basic','post');
    }

    private function post($data,$url){
        return $this->basicRequest($data,$url,'common','post');
    }

    private function get($data,$url){
        return $this->basicRequest($data,$url,'common','get');
    }

    private function basicGet($data,$url){
        return $this->basicRequest($data,$url,'basic','get');
    }

    private function setSid($data){
        isset($data['sid']) && $this->common['sid'] = $data['sid'];
    }

    private function setCourseId($id){
        $this->course_id = $id;
    }

    private function setChapterId($id){
        $this->chapter_id = $id;
    }

    private function setCourseWareId($id){
        $this->course_ware_id = $id;
    }

    private function basicRequest($data,$url,$type='basic',$method='get'){
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
    private function formatPrint($resp_json,$data,$url,$method){
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
    private function underLineToCamel($underline){
        return str_replace(' ','',ucwords(implode(' ',explode('_',$underline))));
    }


    /**
     * 驼峰转下划线方法
     * @param $camel
     * @return string
     */
    private function camelToUnderLine($camel){
        return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $camel));
    }

    //设备接口

    //设备激活
    public function devices_active($url){
        $resp_data = $this->basicPost([],$url);
        $this->setSid($resp_data);
    }

    //版本升级信息获取
    public function softVersions_upgrade($url){
        $this->basicGet([],$url);
    }


    //获取省份接口
    public function provinces($url){
        $this->basicGet([],$url);
    }


    //获取城市接口
    public function cities($url){
        $data = [
            'province_id' =>5
        ];
        $this->basicGet($data,$url);
    }



    //关于我们
    public function productChannels_about($url){
        $this->basicGet([],$url);
    }

    //用户接口

    //请求验证码
//    public function users_sendAuth($url){
//        $this->post([],$url);
//    }
//
//
//    //登录
//    public function users_login($url){
//        $data = $this->post([],$url);
//        $this->setSid($data);
//    }
//
//    public function users_logout($url){
//        $this->post([],$url);
//    }
//
//    public function users_detail($url){
//        $this->get([],$url);
//    }

    public function users_playHistories($url){
        $this->get([],$url);
    }


    //新增系列课程接口
    public function teachers_courseCreate($url){
        $data = [
            'title' => time()."测试课程创建",
            'chapter_num'  => rand(3,8),
            'category_id'  => 1,
            'second_category_id' => 3,
            'price' => rand(10,38),
        ];
        $resp_data = $this->post($data,$url);
        $this->setCourseId($resp_data['id']);
        $this->call('teachers_courseDetail');
    }

    //系列课更新接口
    public function teachers_courseUpdate($url){
        $data = [
            'course_id' => $this->course_id,
            'title' => time().'测试课程更新',
            'description'=>time().'测试课程描述',
            'price' => rand(100,200),
            'surface_image' => $this->image,
            'video' => $this->video,
            'video_image' => $this->image,
            'description_image_num'=>1,
            'surface_image' => $this->image,
            'description_image_0' => $this->image
        ];
        $this->post($data,$url);
//        $this->call('teachers_courseDetail');
    }

    //老师系列课程详情接口
    public function teachers_courseDetail($url){
        $data = [
            'course_id' => $this->course_id
        ];
        $this->get($data,$url);
    }


    //删除课程介绍图片
//    public function teachers_courseImageDelete($url){
//        $data = [
//            'id' => 44
//        ];
//        $this->post($data,$url);
//    }

//    public function teachers_courseRemoveChapter($url){
//        $data = [
//            'chapter_id' => 1
//        ];
//        $this->post($data,$url);
//    }

//    public function teachers_courseAddChapter($url)
//    {
//        $data = [
//            'course_id' => 1,
//            'course_ids' => '[9,10,11]'
//        ];
//        $this->post($data,$url);
//    }

    //新增章节接口
    public function teachers_chapterCreate($url){
        $data = [
            'course_id' => $this->course_id,
            'title' => time()."测试章节创建",
            'category_id'  => 1,
            'second_category_id' => 3,
            'price' => rand(10,38),
            'publis_at' => time()
        ];
        $resp_data = $this->post($data,$url);
        $this->setChapterId($resp_data['id']);
//        $this->call('teachers_chapterDetail');
//        $this->call('teachers_courseDetail');
    }

    //更新章节详情接口
//    public function teachers_chapterUpdate($url){
//        $data = [
//            'chapter_id' => $this->chapter_id,
//            'title' => time().'测试章节更新'
//        ];
//        $this->post($data,$url);
//    }

    //章节详情接口
    public function teachers_chapterDetail($url){
        $data = [
            'chapter_id' => $this->chapter_id
        ];
        $this->get($data,$url);
    }

    //课程章节列表接口
    public function teachers_chapters($url){
        $data = [
            'course_id' => $this->course_id
        ];
        $this->get($data,$url);
    }


    //课件列表接口
    public function courseWares($url){
        $data = [
            'chapter_id' => $this->chapter_id
        ];
        $this->basicGet($data,$url);
    }

    //新增课件接口
    public function courseWares_create($url){
        $data = [
            'chapter_id' => $this->chapter_id,
            'name' => time().'新增课件',
            'image' => $this->image,
            'video' => $this->video,
            'audio' => $this->video,
        ];
        $resp_data = $this->post($data,$url);
        $this->setCourseWareId($resp_data['id']);
//        $this->call('courseWares');
//        $this->call('teachers_courseDetail');
    }

    //更新课件接口
//    public function courseWares_update($url){
//        $data = [
//            'id' => $this->course_ware_id,
//            'image' => $this->image,
//            'name' => time().'更新课件',
//        ];
//        $this->post($data,$url);
//    }

    //课件详情接口
    public function courseWares_detail($url){
        $data = [
            'id' => $this->course_ware_id,
        ];
        $this->post($data,$url);
    }

    //排序列表接口
//    public function courseWares_sort($url){
//        $data = [
//            'ids' => "[".$this->course_ware_id."]"
//        ];
//        $this->post($data,$url);
//    }

    //删除课件接口
//    public function courseWares_delete($url){
//        $data = [
//            'id' => $this->course_ware_id
//        ];
//        $this->post($data,$url);
//        $this->call('courseWares');
//    }




    //我的提问列表
    public function messages_user($url){
        $data = [
            'course_id' => 42
        ];
        $this->get($data,$url);
    }

    //房间的问题列表
    public function messages_room($url){
        $data = [
            'course_id' => 42
        ];
        $this->post($data,$url);
    }


    //提问接口
    public function messages_mail($url){
        $data = [
            'chapter_id' => 29,
            'content' => 'fsdfdsfsdf'
        ];
        $this->post($data,$url);
    }


    //老师的课程问题列表(老师入口)
    public function messages_course($url){
        $data = [
            'course_id' => 1
        ];
        $this->get($data,$url);
    }

    //回复问题接口(老师入口)
//    public function messages_reply($url){
//        $data = [
//            'first_message_id' => 1,
//            'content' => '回复测试信息'
//        ];
//        $this->post($data,$url);
//    }


    //获取Banners接口
    public function banners($url){
        $this->get([],$url);
    }


    //点击Banner上报接口
    public function banners_click($url){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }



    //获取分类接口
    public function categories($url){
        $this->basicGet([],$url);
    }



    //获取一级分类接口
    public function categories_first($url){
        $this->basicGet([],$url);
    }


    //活动专区列表接口
    public function saleZones($url){
        $this->basicGet([],$url);
    }

    //课程列表接口
    public function courses_search($url){
        $this->basicGet([],$url);
    }

    //热门推荐接口
    public function courses_hot($url){
        $this->basicGet([],$url);
    }

    //课程详情接口
    public function courses_detail($url){
        $data = [
            'id' => 42
        ];
        $this->basicGet($data,$url);
    }

    //课程章节列表接口
    public function chapters($url){
        $data = [
            'course_id' => 1
        ];
        $this->basicGet($data,$url);
    }


    //章节详情接口
    public function chapters_detail($url){
        $data = [
            'id' => 1
        ];
        $this->basicGet($data,$url);
    }


    //房间接口
    public function chapters_room($url){
        $data = [
            'id' => 1
        ];
        $this->post($data,$url);
    }

    //播放上报接口
    public function chapters_play($url){
        $data = [
            'course_ware_id' => 100
        ];
        $this->post($data,$url);
    }

    private function call($function_name){
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
//$preach = new Preach();
//$preach->exec();

$curl = new Curl("http://study.com/elasticsearch/search.php");
echo $curl->post();
$url ="http://preach.com/api/course_wares";
$query_params = [
    'debug'=>1,
    'code'=>'tret',
    'sid'=>'7s4a5f5ed6f8b679880591bbb68be82e2fbe',
    'chapter_id'=>29
];
$curl->setUrl($url);
echo $curl->get($query_params);

?>