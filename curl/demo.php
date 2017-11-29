<?php
include("../functions.php");
include("Curl.class.php");

function requestApi($action,$query,$type='get'){
    $url = 'http://sandbox-api.e.qq.com/v1.0/';
    $url .= $action;
    $data = [];
    $data['timestamp'] = time();
    $data['access_token'] = "66c6f01612c61ce6ca354226ef1b4489";
    $data['nonce'] = uniqid();

    //$query = array_merge($data,$query);
    $url .= '?'.http_build_query($data);
    $curl = new Curl($url);
    if($type=='get'){
        $json = $curl->getContent();
    }else{
        $json = $curl->post($query);
    }
    return json_decode($json,true);
}
//$json = '{"account_id":100009,"name":"curl_add","description":"curl_add","type":"CUSTOMER_FILE"}';
//$custom_audience = json_decode($json,true);
//$custom_audience['audience_spec'] = json_encode($custom_audience['audience_spec']);
//p($custom_audience);
//$resp = requestApi('custom_audiences/add',$custom_audience,'post');
//p($resp);die;

print_r(explode(',','fdsfds,fdsfd'));

echo implode(',',['dingd']);

?>







