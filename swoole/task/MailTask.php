<?php
require __DIR__.DIRECTORY_SEPARATOR.'AbstractTask.php';
/**
 * 邮件发送任务
 * Class MailTask
 */
class MailTask extends AbstractTask{
    public function run($data)
    {
        print_r($data);
    }
}