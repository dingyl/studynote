<?php
include "./db.php";
error_reporting(E_ALL & ~E_NOTICE);
class Postgres extends Db {
    /**
     * 获取数据表的字段
     */
    public function _field($tb_name){

    }


    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit,$offset=0){
        if(!empty($limit)){
            $this->_limit = " limit $limit, offset $offset ";
        }else{
            $this->_limit = '';
        }
        return $this;
    }
}

function M($tb_name=''){
    return Postgres::getIns('')->table($tb_name);
}
//插入数据没有问题
//更新数据有问题   in  条件方法
$model = M("users");
$where = ['name'=>'ding','bug'=>['>',23],'age'=>['in',array(1,2,3)],'email'=>['like','230@qq'],'height'=>['between',23,34]];
//$status = $model->field('id,name')->order('id')->limit(2)->find($where);
//$data = $model->find();
//p($data);
phpinfo();



//var_dump($status);