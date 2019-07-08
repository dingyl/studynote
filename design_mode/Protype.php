<?php

require_once '../utils.php';

# 原型模式

class AdvTemplate
{
    private $adv_subject;
    private $adv_context;

    public function __construct($adv_subject, $adv_context)
    {
        $this->adv_subject = $adv_subject;
        $this->adv_context = $adv_context;
    }


    public function getAdvSubject()
    {
        return $this->adv_subject;
    }

    public function getAdvContext()
    {
        return $this->adv_context;
    }
}


class Mail
{

    # 收件人
    private $receiver;

    # 称谓
    private $appellation;

    # 内容
    private $context;

    # 邮件名称
    private $subject;

    # 邮件尾部
    private $tail;

    # 广告模板
    private $adv_template;

    public function __construct(AdvTemplate $adv_template)
    {
        $this->context = $adv_template->getAdvContext();
        $this->subject = $adv_template->getAdvSubject();
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    public function getAppellation()
    {
        return $this->appellation;
    }

    public function setAppellation($appellation)
    {
        $this->appellation = $appellation;
    }

    public function getTail()
    {
        return $this->tail;
    }

    public function setTail($tail)
    {
        $this->tail = $tail;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function __clone()
    {
        $this->adv_template = clone $this->adv_template;
    }
}

class Client
{
    public static function sendMail(Mail $mail)
    {
        echoLine('标题' . $mail->getSubject() . ' 收件人' . $mail->getReceiver() . ' 发送成功');
    }
}


$adv_template = new AdvTemplate('广告', '优惠办理信用卡');

$mail = new Mail($adv_template);

for ($i = 0; $i <= 100; $i++) {
    $temp_mail = clone $mail;
    $temp_mail->setReceiver($i . 'receiver');
    $temp_mail->setAppellation($i . 'appellation');
    Client::sendMail($temp_mail);
}



