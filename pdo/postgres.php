<?php
include "./db.php";
error_reporting(E_ALL & ~E_NOTICE);

class Postgres extends Db {

    /**
     * 获取数据表的字段
     */
    public function test(){
        echo "this is test";
        /*$ret = $this->_pdo->query("desc user");
        $temp = [] ;
        $key = '' ;
        foreach($ret as $v){
            $v['Key']=='PRI' && $key=$v['Field'] ;
            $temp[] = $v['Field'] ;
        }
        $this->_opt['key'] = $key ;
        $this->_opt['field'] = $temp ;*/
    }
}

function M($tb_name=''){
    return Postgres::getIns()->table($tb_name);
}
//插入数据没有问题
//in  条件方法   pdo不支持
$model = M("demotable");
$model->find();

//
//
////添加日志功能
//$where = ['name'=>'ding','bug'=>['>',23],'age'=>['in',array(1,2,3)],'email'=>['like','230@qq'],'height'=>['between',23,34]];
////$status = $model->field('id,name')->order('id')->limit(2)->find($where);
//$data = $model->find(['name'=>'ding']);
//p($data);
//var_dump($status);