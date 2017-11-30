<?php
namespace app\controllers;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use app\models\User;
use yii;
class IndexController extends Controller{
    public function actionIndex(){
        $model = new User();
        /*$dataProvider = new ActiveDataProvider([
            'query' => $model->find(),
            'pagination' => [
                'pagesize' => 1,
            ],
        ]);

        return $this->render('index',['model'=>$model,'dataProvider'=>$dataProvider]);

        $model->username=uniqid();
        $model->password=md5('123456');
        $model->email = '190@qq.com';
        $model->repassword = md5('123456');
        if($model->validate())
        {

        }else{
            print_r($model->errors);
        }

        print_r(Yii::$app->request->get());

        print_r($model->attributes);*/

        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $model->save();
            echo $model->id;
        }else{
            return $this->render('index',['model'=>$model]);
        }
    }

    public function actionAdd(){
        $model = new User();

    }
}