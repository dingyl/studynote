<?php
require_once("Email.php");
$address = "1907928206@qq.com";
$mail = Email::getIns();
$mail->addAddress($address);
$mail->setTitle("打好第一张");
$mail->sendMail('准备好了吗');