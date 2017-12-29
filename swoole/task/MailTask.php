<?php
require __DIR__.DIRECTORY_SEPARATOR.'AbstractTask.php';
require __DIR__.'/../../PHPMailer/Email.php';
/**
 * 邮件发送任务
 * Class MailTask
 */
class MailTask extends AbstractTask{
    public function run($data)
    {
        $address = $data['address'];
        $title = $data['title'];
        $content = $data['content'];
        $mail = Email::getIns();
        $mail->addAddress($address);
        $mail->setTitle($title);
        $mail->sendMail($content);
    }
}