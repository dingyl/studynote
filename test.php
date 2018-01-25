<?php
require_once __DIR__."/Magick.php";
$ppt = __DIR__."/test.ppt";
$pngs = Magick::ppt2png($ppt);
if($pngs){
    foreach ($pngs as $png){
        //上传图片到阿里云
        unlink($png);
    }
}
echo "<pre>";
print_r($pngs);