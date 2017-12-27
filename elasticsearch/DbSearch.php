<?php
require __DIR__.'/Elastic.php';
/**
 * 数据库全文搜索助手
 * Class DbSearch
 */
class DbSearch{
    public static $ins;
    protected $client;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public static function getIns($hosts,$index,$type){
        if(!static::$ins instanceof static){
            static::$ins = new static();
            static::$ins->client = Elastic::getIns($hosts,$index,$type);
        }
        return static::$ins;
    }

    public function initTable($fields){
        $this->client->createType($this->client->getType(),$fields);
        return $this;
    }

    public function save($data){
        $this->client->add($data);
        return $this;
    }

    public function del($id){
        $this->client->delete($id);
        return $this;
    }

    public function clear(){
        $data = $this->client->find();
        if($count = $data['total']){
            for($i=0;$i<$count;$i++){
                $this->del($data[$i]['_id']);
            }
        }
    }

    public function find($cond){
        return $this->client->find($cond);
    }

    public function findAll(){
        return $this->client->find();
    }

    public function findById($id){
        return $this->client->get($id);
    }

    public function tag($tag){
        $this->client->tag($tag);
        return $this;
    }

    public function stat($aggs,$cond=[]){
        return $this->client($aggs,$cond);
    }

    public function limit($limit, $offset = 0){
        $this->client->limit($limit,$offset);
        return $this;
    }

    public function group($field){
        $this->client->group($field);
        return $this;
    }
}