<?php
namespace app\models;
use yii\db\ActiveRecord;
use yii;
class User extends ActiveRecord
{
    public $repassword;

    public static function tableName()
    {
        return "{{%user}}";
    }

    public function rules ()
    {
        return [
            [['username','password'],'required'],
            ['email','email'],
            ['repassword','compare','compareAttribute'=>'password']
        ];
    }

    public function attributeLabels ()
    {
        /*return [
            'username'=>'用户名',
            'password'=>'密码',
            'email'=>'邮箱',
        ];*/

        return [
            //找到messages下的language目录下的app.php文件,如果username有相对应的值,返回值,否则返回username
            'username'=>Yii::t('app','username'),
            'password'=>Yii::t('app','password'),
            'repassword'=>Yii::t('app','repassword'),
        ];
    }
}