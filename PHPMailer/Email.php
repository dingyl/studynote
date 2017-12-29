<?php
require __DIR__.'/class.phpmailer.php';
require __DIR__.'/class.pop3.php';
require __DIR__.'/class.smtp.php';
define('SERVER_EMAIL_HOST','smtp.qq.com');
define('SERVER_EMAIL_USERNAME','1907928206@qq.com');
define('SERVER_EMAIL_PASSWORD','wkjkusszeynqcjii');
define('SERVER_EMAIL_NICKNAME','青色火焰');
class Email{
    private static $ins;
    private $mail;
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getIns(){
        if(!self::$ins instanceof static){
            self::$ins = new static();
            self::$ins->mail = new PHPMailer();
            self::$ins->mail->isSMTP();
            self::$ins->mail->SMTPAuth=true;
            self::$ins->mail->Host = SERVER_EMAIL_HOST;
            self::$ins->mail->SMTPSecure = 'ssl';
            //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
            self::$ins->mail->Port = 465;
            self::$ins->mail->CharSet = 'UTF-8';
            //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
            self::$ins->mail->FromName = SERVER_EMAIL_NICKNAME;
            self::$ins->mail->Username = SERVER_EMAIL_USERNAME;
            self::$ins->mail->Password = SERVER_EMAIL_PASSWORD;
            self::$ins->mail->From = SERVER_EMAIL_USERNAME;
            self::$ins->mail->isHTML(true);
        }
        return self::$ins;
    }

    /**
     * 发送邮件
     * @param $content
     * @return mixed
     */
    public function sendMail($content){
        $this->mail->Body = $content;
        return $this->mail->send();
    }

    /**
     * 添加地址
     * @param $email_address
     */
    public function addAddress($email_address){
        $this->mail->addAddress($email_address);
    }

    /**
     * 设置主题
     * @param $title
     */
    public function setTitle($title){
        $this->mail->Subject = $title;
    }

    /**
     * 添加附件
     * @param $file
     */
    public function addAttach($file){
        $this->mail->addAttachment($file);
    }

}