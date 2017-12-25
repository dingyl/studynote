<?php
require '../functions.php';
require 'vendor/autoload.php';
class Elastic
{
    public static $ins ;
    protected $client ;
    protected $index ;
    protected $type ;
    protected $limit = 10000;
    protected $offset = 0;
    protected $tag = ['<span style="color: red">', '</span>'];
    protected $field = [];
    protected $group = '';
    protected $order = [];
    protected $ignore_params = [
        'client' => [
            'ignore' => [
                400, 404, 500
            ]
        ]
    ];

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @param $hosts    ['localhost:9200']
     * @param string $index
     * @param string $type
     * @return mixed
     */
    public static function getIns($hosts, $index = '', $type = '')
    {
        if (!static::$ins instanceof static) {
            static::$ins = new self();
            static::$ins->client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
            static::$ins->index = $index;
            static::$ins->type = $type;
        }
        return static::$ins;
    }


    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }


    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType(){
        return $this->type;
    }


    public function getInfo($index_name = '', $type_name = '')
    {
        $params = [];
        if (!empty($index_name)) {
            if ($this->existsIndex($index_name)) {
                $params['index'] = $index_name;
                if (!empty($type_name)) {
                    if ($this->existsType($type_name)) {
                        $params['type'] = $type_name;
                    } else {
                        return [];
                    }
                }
            } else {
                return [];
            }

        };
        return $this->client->indices()->getMapping($this->combineParams($params));
    }


    public function createIndex($index_name)
    {
        $params = [
            'index' => $index_name
        ];
        $this->setIndex($index_name);
        return $this->client->indices()->create($this->combineParams($params));
    }

    public function deleteIndex($index_name)
    {
        $params = [
            'index' => $index_name
        ];
        return $this->client->indices()->delete($this->combineParams($params));
    }

    public function existsIndex($index_name)
    {
        $params = [
            'index' => $index_name
        ];
        return $this->client->indices()->exists($this->combineParams($params));
    }

    public function existsType($type_name)
    {
        return in_array($type_name, $this->types($this->index));
    }

    public function setIndexSetting($setting)
    {
        $params = [
            'index' => $this->index,
            'body' => $setting
        ];
        $this->client->putSettings($this->combineParams($params));
        return $this;
    }

    public function getIndexSetting($index_name)
    {
        if (!$this->existsIndex($index_name)) {
            return false;
        }
        $params = [
            'index' => $index_name
        ];
        return $this->client->indices()->getSetting($this->combineParams($params));
    }

    public function flushAll(){
        foreach($this->indexs() as $index){
            $this->deleteIndex($index);
        }
        return $this;
    }

    /**
     * 获取所有的index
     * @return array
     */
    public function indexs()
    {
        return array_keys($this->getInfo());
    }

    /**
     * 获取指定index下的所有type
     * @param $index_name
     * @return array
     */
    public function types($index_name)
    {
        $types = [];
        if ($this->existsIndex($index_name)) {
            $types = array_keys($this->getInfo()[$index_name]['mappings']);
        }
        return $types;
    }

    /**
     * 在指定index下创建type，并指定字段对应的分词引擎
     * @param $type_name
     * @param $fields   ['field_name'=>'field_type']
     * @return bool
     */
    public function createType($type_name, $fields)
    {
        if (!$this->existsIndex($this->index)) {
            return false;
        }
        $properties = [];
        foreach ($fields as $field_name => $field_type) {
            if (in_array($field_type, ['string', 'text'])) {
                $properties[$field_name] = [
                    'analyzer' => 'ik_max_word',
                    'type' => 'text'
                ];
            }

            if (in_array($field_type, ['int', 'integer'])) {
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


    /**
     * 向指定的index下的type添加数据
     * @param $data
     * @param null $_id
     * @return bool
     */
    public function add($data, $_id = NULL)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => $data
        ];
        if (isset($data['id'])) {
            $params['id'] = $data['id'];
        }
        if (!empty($_id)) {
            $params['id'] = $_id;
        }
        $res = $this->client->index($this->combineParams($params));
        if ($res['created']) {
            return $res['_id'];
        } else {
            return false;
        }
    }


    /**
     * 根据id获取指定index下type中的数据
     * @param $_id
     * @return array
     */
    public function get($_id)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        $res = $this->client->get($this->combineParams($params));
        $data = [];
        if ($res['found']) {
            $data = $res['_source'];
        }
        return $data;
    }

    /**
     * 判断数据是否存在
     * @param $_id
     * @return mixed
     */
    public function exists($_id)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        return $this->client->exists($this->combineParams($params));
    }

    /**
     * 删除数据
     * @param $_id
     * @return mixed
     */
    public function delete($_id)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $_id
        ];
        return $this->client->delete($this->combineParams($params));
    }

    /**
     * 更新数据，可以用add替代
     * @param $data
     * @param null $_id
     * @return mixed
     */
    public function update($data, $_id = NULL)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'doc' => $data
            ]
        ];
        if (isset($data['id'])) {
            $params['id'] = $data['id'];
        }
        if (!empty($_id)) {
            $params['id'] = $_id;
        }
        return $this->client->update($this->combineParams($params));
    }

    /**
     * 获取指定index,type所有的字段
     * @return array
     */
    public function getFields()
    {
        $res = [];
        if ($this->existsIndex($this->index) && $this->existsType($this->type)) {
            $fields = $this->getInfo()[$this->index]['mappings'][$this->type]['properties'];
            $res = array_keys($fields);
        }
        return $res;
    }


    public function limit($limit, $offset = 0)
    {
        $this->limit = $limit > 10000 ? 10000 : $limit;
        $this->offset = $offset;
        return $this;
    }


    /**
     * 设置查询选中字段标签元素
     * @param $tags
     * @return $this
     */
    public function tag($tags)
    {
        $this->tag = $tags;
        return $this;
    }

    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    public function field($field)
    {
        $this->field = $field;
        return $this;
    }

    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * 条件查询
     * @param array $condition  ['age' => ['gt'=>10,'lt'=>20]]
     * @return array
     */
    public function find($condition = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'from' => $this->offset,
            'size' => $this->limit
        ];

        $_fields = $this->getFields();

        //排序处理
        if (!empty($this->order)) {
            $sort = [];
            foreach ($this->order as $field => $esc) {
                if (in_array($field, $_fields)) {
                    $sort[] = [
                        $field => [
                            "order" => $esc
                        ]
                    ];
                }
            }
            if (!empty($sort)) {
                $params['body']['sort'] = $sort;
            }
        }

        if($query = $this->getConditions($condition)){
            $params['body']['query'] = $query;
        }

        //显示字段设置
        $temp_fields = empty($this->field) ? $_fields : $this->field;
        $fields = [];
        foreach ($temp_fields as $field_name) {
            $fields[$field_name] = new \stdClass();
        }
        $params['body']['_source'] = [
            "include" => $temp_fields
        ];

        //高亮查询字符串
        if (!empty($this->tag)) {
            $params['body']['highlight'] = [
                'pre_tags' => [$this->tag[0]],
                'post_tags' => [$this->tag[1]],
                'fields' => $fields
            ];
        }

        $res = $this->client->search($this->combineParams($params));
        return $this->getData($res);
    }


    /**
     * 过滤统计
     * 获取聚合值 min,max,sum,avg
     * $aggs = [['stats'=>'age'],['max'=>'age'],[ 'range' => ['age'=>[ ['to'=>30],['from'=>10],['from'=>10,'to'=>30] ] ]]]
     * $conditoin = [['username'=>'user']]
     * 统计查询信息
     * @param $aggs
     * @param array $condition
     * @return array
     */
    public function aggs($aggs, $condition = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type
        ];

        if($query = $this->getConditions($condition)){
            $params['body']['query'] = $query;
        }

        //group 分组条件  简单分组  不指定size默认就显示10个分组数据
        if (!empty($this->group)) {
            $params['body']['aggs']['terms_' . $this->group]['terms'] = [
                'field' => $this->group,
                'size' => 10000
            ];
        }

        //统计设置
        foreach ($aggs as $agg) {
            list($operator, $field) = each($agg);
            if ($operator == 'range' && is_array($field)) {
                list($field, $value) = each($field);
                $params['body']['aggs']['range_' . $field]['range'] = [
                    'field' => $field,
                    'ranges' => $value
                ];
            } else {
                $params['body']['aggs'][$operator . '_' . $field] = [$operator => ['field' => $field]];
            }
        }

        $data = [];
        $res = $this->client->search($this->combineParams($params));

        if(isset($res['error'])){
            $res['error']['status'] = $res['status'];
            return $res['error'];
        }

        //返回统计结果
        if (isset($res['aggregations'])) {
            $data = $res['aggregations'];
        }

        return $data;
    }


    /**
     * 格式化条件
     * @param $condition
     * @param string $type [match|filter]
     * @return array
     */
    public function getConditions($condition,$type='match'){
        $query = [];
        foreach ($condition as $field => $value) {
            if (is_array($value)) {
                $query['range'] = [
                    $field => $value
                ];
                unset($condition[$field]);
            } else {
                if($type=='match'){
                    $query['bool']['must'][] = [
                        'match' => ["$field" => $value]
                    ];
                }

                if($type=='filter'){
                    $query['bool']['filter'][] = [
                        'term' => ["$field" => $value]
                    ];
                }
            }
        }
        return $query;
    }


    /**
     * 返回所有的文档
     * @return array
     */
    public function getDocuments()
    {
        $res = $this->client->search();
        return $this->getData($res);
    }

    /**
     * 获取返回信息中的数据信息
     * @param $res
     * @return array
     */
    protected function getData($res)
    {
        $data = [];

        if(isset($res['error'])){
            $res['error']['status'] = $res['status'];
            return $res['error'];
        }

        $data['total'] = $res['hits']['total'];
        if (isset($res['hits'])) {
            foreach ($res['hits']['hits'] as $v) {
                $v['_source']['_id'] = $v['_id'];
                foreach ($this->getFields() as $field) {
                    if (isset($v['highlight'][$field])) {
                        $v['_source']['_' . $field] = $v['highlight'][$field][0];
                    }
                }
                $data[] = $v['_source'];
            }
        }
        return $data;
    }


    /**
     * 组合参数
     * @param $params
     * @return array
     */
    protected function combineParams($params)
    {
        $params = array_merge($params, $this->ignore_params);
        return $params;
    }
}