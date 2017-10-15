<?php
//上传文件处理
//返回数据格式
/*{
    // errno 即错误代码，0 表示没有错误。
    //       如果有错误，errno != 0，可通过下文中的监听函数 fail 拿到该错误码进行自定义处理
    errno: 0,
    // data 是一个数组，返回若干图片的线上地址
    data: [
    '图片1地址',//服务器上的路径
    '图片2地址',
    '……'
    ]
}*/

$data = array('errno'=>0,'data'=>array('filedsjfd','fdsfsdfdsf','fdsfdsfdsf'));
echo json_encode($data);