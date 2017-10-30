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
    public function limit(int $limit,int $offset=0){
        if(!empty($limit)){
            $this->_limit = " limit $limit, offset $offset ";
        }else{
            $this->_limit = '';
        }
        return $this;
    }

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
    return Postgres::getIns('')->table($tb_name);
}

$model = M("users");

$where = ['name'=>'ding','bug'=>['>',23],'age'=>['in',array(1,2,3)],'email'=>['like','230@qq'],'height'=>['between',23,34]];
//$status = $model->field('id,name')->order('id')->limit(2)->find($where);
$model->update(['name'=>'test'],['id'=>20]);
//var_dump($status);