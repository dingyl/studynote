<?php
require __DIR__.DIRECTORY_SEPARATOR.'phpqrcode.php';
$value="http://localhost/TinyShop/index.php?con=index&act=category&cid=2&sort=0&price=2200-3199";//想要生成的二维码信息
$errorCorrectionLevel = "H";  //L|M|Q|H
$matrixPointSize = "4";
QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
exit;//这样将生成一张二维码图片