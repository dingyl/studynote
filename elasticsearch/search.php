<?php
require '../functions.php';
require 'vendor/autoload.php';

class Elastic{
    private $client = NULL;
    private $index = NULL;
    private $type = NULL;
    private $default_params = [
        'client' => [
            'ignore' => [
                400,404,500
            ]
        ]
    ];

    public function __construct($hosts,$index=NULL,$type=NULL){
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


    //[['name'=>'','type'=>'']['name'=>'','type'=>'']]
    public function createType($type_name,$fields){
        if(!$this->existsIndex($this->index)){
            return false;
        }
        $properties = [];
        foreach($fields as $field){
            if(in_array($field['type'],['string','text'])){
                $properties[$field['name']] = [
                    'analyzer' => 'ik_max_word',
                    'type' => 'text'
                ];
            }

            if(in_array($field['type'],['int','integer'])){
                $properties[$field['name']] = [
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
        if(!empty($_id)){
            $params['id'] = $_id;
        }
        return $this->client->index($this->combineParams($params));
    }

    public function get($_id){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        return $this->client->get($this->combineParams($params));
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

    public function update($_id,$data){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id,
            'body' => [
                'doc' => $data
            ]
        ];
        return $this->client->update($this->combineParams($params));
    }

    /**
     * [key=>[1,2,3]]
     * 返回指定条件下的所有文档
     * @param array $query
     * @return array
     */
    public function search($conditions=[],$fields=[],$tag='',$limit=NULL,$offset=0){

        if(!empty($limit)){
            $body['size'] = $limit;
        }

        $params = [
            'index' => $this->index,
            'type' => $this->type
        ];

        if(!empty($conditions)){
            $matchs = [];
            foreach($conditions as $field=>$value){
                if(is_array($value)){
                    $value = implode(' ',$value);
                }
                $matchs[] = "{'match' : { '$field' : '$value' } }";
            }

            $matchs_str = '[ '.implode(', ',$matchs).' ]';

            $body = "{
                'query' : {
                    'bool' : {
                        'must' : $matchs_str
                    }
                }
            }";
            $params['body'] = $body;
        }

        $res = $this->client->search($this->combineParams($params));
        $data = [];
        if(isset($res['hits'])){
            foreach($res['hits']['hits'] as $v){
                $v['_source']['_id'] = $v['_id'];
                $data[] = $v['_source'];
            }
        }
        return $data;
    }

    /**
     * 返回所有的文档
     * @return array
     */
    public function getDocuments(){
        $res = $this->client->search();
        $data = [];
        if(isset($res['hits'])){
            foreach($res['hits']['hits'] as $v){
                $v['_source']['_id'] = $v['_id'];
                $data[] = $v['_source'];
            }
        }
        return $data;
    }

    protected function combineParams($params){
        p($params);
        return array_merge($this->default_params,$params);
    }
}

$hosts = ['localhost:9200'];
$client = new Elastic($hosts,'blog','article');
//p($client->getInfo());

$conditions = ['title'=>['习近平', '黑洞'],'text'=>'习近平'];

p($client->search());die;

//p($client->createType('article',$fields));

p($client->getInfo());
