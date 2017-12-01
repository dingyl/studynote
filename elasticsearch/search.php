<?php
require '../functions.php';
require 'vendor/autoload.php';

class Elastic{
    private $client = NULL;
    private $index = NULL;
    private $type = NULL;
    //$limit = 10000 接口默认最大值为10000
    private $limit = 10000;
    private $offset = 0;
    private $tag = ['',''];
    private $field = [];
    private $group = '';
//    private $where = [];
    private $order = [];
    //限制报错
    private $default_params = [
        'client' => [
            'ignore' => [
                400,404,500
            ]
        ]
    ];

    public function __construct($hosts,$index='',$type=''){
        $this->client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->index = $index;
        $this->type = $type;
    }


    public function setIndex($index){
        $this->index = $index;
        return $this;
    }


    public function setType($type){
        $this->type = $type;
        return $this;
    }



    public function getInfo($index_name='',$type_name=''){
        $params = [];
        if(!empty($index_name)) {
            if($this->existsIndex($index_name)){
                $params['index'] = $index_name;
                if(!empty($type_name)){
                    if($this->existsType($type_name)){
                        $params['type'] = $type_name;
                    }else{
                        return [];
                    }
                }
            }else{
                return [];
            }

        };
        return $this->client->indices()->getMapping($this->combineParams($params));
    }


    public function createIndex($index_name){
        $params = [
            'index'=>$index_name
        ];
        $this->setIndex($index_name);
        return $this->client->indices()->create($this->combineParams($params));
    }

    public function deleteIndex($index_name){
        $params = [
            'index'=>$index_name
        ];
        return $this->client->indices()->delete($this->combineParams($params));
    }

    public function existsIndex($index_name){
        $params = [
            'index' => $index_name
        ];
        return $this->client->indices()->exists($this->combineParams($params));
    }

    public function existsType($type_name){
        return in_array($type_name,$this->types($this->index));
    }

    public function setIndexSetting($setting){
        $params = [
            'index' => $this->index,
            'body' => $setting
        ];
        $this->client->putSettings($this->combineParams($params));
        return $this;
    }

    public function getIndexSetting($index_name){
        if(!$this->existsIndex($index_name)){
            return false;
        }
        $params = [
            'index'=>$index_name
        ];
        return $this->client->indices()->getSetting($this->combineParams($params));
    }


    public function indexs(){
        return array_keys($this->getInfo());
    }

    public function types($index_name){
        $types = [];
        if($this->existsIndex($index_name)){
            $types = array_keys($this->getInfo()[$index_name]['mappings']);
        }
        return $types;
    }


    //['field_name'=>'field_type']
    public function createType($type_name,$fields){
        if(!$this->existsIndex($this->index)){
            return false;
        }
        $properties = [];
        foreach($fields as $field_name=>$field_type){
            if(in_array($field_type,['string','text'])){
                $properties[$field_name] = [
                    'analyzer' => 'ik_max_word',
                    'type' => 'text'
                ];
            }

            if(in_array($field_type,['int','integer'])){
                $properties[$field_name] = [
                    'type' => 'integer'
                ];
            }
        }
        $params = [
            'index' => $this->index,
            'type' => $type_name,
            'body' => [
                "$type_name" => [
                    '_all' => [
                        'enabled' => 'false'
                    ],
                    'properties' => $properties
                ]
            ]
        ];
        $this->setType($type_name);
        return $this->client->indices()->putMapping($this->combineParams($params));
    }


    public function add($data,$_id=NULL){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => $data
        ];
        if(isset($data['id'])){
            $params['id'] = $data['id'];
        }
        if(!empty($_id)){
            $params['id'] = $_id;
        }
        $res = $this->client->index($this->combineParams($params));
        if($res['created']){
            return $res['_id'];
        }else{
            return false;
        }
    }

    public function get($_id){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        $res = $this->client->get($this->combineParams($params));
        $data = [];
        if($res['found']){
            $data = $res['_source'];
        }
        return $data;
    }

    public function exists($_id){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        return $this->client->exists($this->combineParams($params));
    }

    public function delete($_id){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        return $this->client->delete($this->combineParams($params));
    }

    public function update($data,$_id=NULL){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'doc' => $data
            ]
        ];
        if(isset($data['id'])){
            $params['id'] = $data['id'];
        }
        if(!empty($_id)){
            $params['id'] = $_id;
        }
        return $this->client->update($this->combineParams($params));
    }

    /**
     * 获取指定index,type所有的字段
     * @return array
     */
    public function getFields(){
        $res = [];
        if($this->existsIndex($this->index)){
            if($this->existsType($this->type)){
                $fields = $this->getInfo()[$this->index]['mappings'][$this->type]['properties'];
                $res = array_keys($fields);
            }
        }
        return $res;
    }


    public function limit($limit,$offset=0){
        $this->limit = $limit>10000 ? 10000 : $limit;
        $this->offset = $offset;
        return $this;
    }

    public function tag($tags){
        $this->tag = $tags;
        return $this;
    }

    public function group($group){
        $this->group = $group;
        return $this;
    }

    public function field($field){
        $this->field = $field;
        return $this;
    }

    public function order($order){
        $this->order = $order;
        return $this;
    }

    //指定需要统计信息的字段
    public function stats($fields){

    }



    //query 获取条件查询结果集     ['age' => ['gt'=>10,'lt'=>20]];
    public function find($condition=[]){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'from' => $this->offset,
            'size' => $this->limit
        ];

        //获取所有字段
        $_fields = $this->getFields();

        //排序
        if(!empty($this->order)){
            $sort = [];
            foreach($this->order as $field=>$esc){
                if(in_array($field,$_fields)){
                    $sort[] = [
                        $field => [
                            "order" => $esc
                        ]
                    ];
                }
            }
            if(!empty($sort)){
                $params['body']['sort'] = $sort;
            }
        }


        //查询过滤
        $query = [];
        foreach($condition as $field=>$value){
            if(is_array($value)){
                //范围限制
                $params['body']['query']['range'] = [
                    $field => $value
                ];
                unset($condition[$field]);
            }else{
                $query[] = [
                    'match' => [ "$field" => $value ]
                ];
            }
        }


        if(!empty($condition)){
            $params['body']['query']['bool']['must'] = $query;
        }


        //显示字段设置
        $temp_fields = empty($this->field) ? $_fields : $this->field ;
        $fields = [];
        foreach($temp_fields as $field_name){
            $fields[$field_name] = new \stdClass();
        }
        $params['body']['_source'] = [
            "include" => $temp_fields
        ];

        //高亮查询字符串
        if(!empty($this->tag)){
            $params['body']['highlight'] = [
                'pre_tags' => [$this->tag[0]],
                'post_tags' => [$this->tag[1]],
                'fields' => $fields
            ];
        }

        $res = $this->client->search($this->combineParams($params));
        return $this->getData($res);
    }


    //filter 可以配合聚合函数进行条件统计
    //获取聚合值 min,max,sum,avg
    //聚合操作 $aggs = [['stats'=>'age'],['max'=>'age','min'=>'age']]
    //$conditoin = [['username'=>'user'],['age'=>[ ['to'=>30],['from'=>10],['from'=>10,'to'=>30] ]]]
    public function aggs($aggs,$condition=[]){
        $params = [
            'index' => $this->index,
            'type' => $this->type
        ];
        $filter = [];
        foreach($condition as $field=>$value){
            if(is_array($value)){
                $params['body']['aggs']['range_'.$field]['range'] = [
                    'field' => $field,
                    'ranges' => $value
                ];
            }else{
                $filter[] = [
                    'term' => [ "$field" => $value ]
                ];
            }
        }

        //group 分组条件  简单分组  不指定size默认就显示10个分组数据
        if(!empty($this->group)){
            $params['body']['aggs']['terms_'.$this->group]['terms'] = [
                'field' => $this->group,
                'size' => 10000
            ];
        }

//        p($params);


        //过滤条件
        if(!empty($condition)){
            $params['body']['query']['bool']['filter'] = $filter;
        }


        //统计设置
        foreach($aggs as $agg){
            foreach($agg as $operator=>$field){
                $params['body']['aggs'][$operator.'_'.$field] = [ $operator=> [ 'field' => $field ] ];
            }
        }

        $data = [];
        $res = $this->client->search($this->combineParams($params));


        //返回统计结果
        if(isset($res['aggregations'])){
            $data = $res['aggregations'];
        }

        return $data;
    }


    /**
     * 返回所有的文档
     * @return array
     */
    public function getDocuments(){
        $res = $this->client->search();
        return $this->getData($res);
    }


    /**
     * 同步数据库数据
     */
    public function syncSqlSource(){

    }

    protected function getData($res){
//        p($res);//die;
        $data = [];
        $data['total'] = $res['hits']['total'];
        if(isset($res['hits'])){
            foreach($res['hits']['hits'] as $v){
                $v['_source']['_id'] = $v['_id'];
                foreach($this->getFields() as $field){
                    if(isset($v['highlight'][$field])){
                        $v['_source']['_'.$field] = $v['highlight'][$field][0];
                    }
                }
                $data[] = $v['_source'];
            }
        }
        return $data;
    }

    protected function combineParams($params){
        $params = array_merge($params,$this->default_params);
//        p($params);
        return $params;
    }
}

$hosts = ['localhost:9200'];
$client = new Elastic($hosts,'test','user');
$cond = ['text'=>'习近平'];
$tag = ['-strong-','-/strong-'];

$user = [
    'id' => 'fsdfdsf',
    'username' => 'fdsf',
    'age' => 45
];

//$client->createType('comment',$fields);

//p($client->add($user));
$order = [
    'id'=>'desc',
    'age'=>'asc'
];


//todo  统计问题
p($client->group('age')->aggs([['stats'=>'age'],['max'=>'age','min'=>'age']],[ 'age'=>[ ['to'=>30],['from'=>10],['from'=>10,'to'=>30] ] ]));
//p($client->find(['age'=>['lt'=>13,'gt'=>10]]));
