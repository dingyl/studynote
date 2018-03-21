<?php
require __DIR__ . '/vendor/autoload.php';

require_once "BaseModel.php";

class ElasticModel extends BaseModel
{
    protected $client;
    protected $index;
    protected $type;
    protected static $ignore_params = [
        'client' => [
            'ignore' => [
                400, 404, 500
            ]
        ]
    ];

    public function __construct()
    {
        $this->index = static::indexName();
        $this->type = static::tableName();
        $this->client = self::client();
    }

    protected static function client()
    {
        $hosts = self::hosts();
        return Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    }

    protected static function hosts()
    {
        return ['127.0.0.1:9200'];
    }

    protected static function indexName()
    {
        return 'xyii_search';
    }

    public static function tableName()
    {
        return 'category';
    }

    public function insert()
    {
        $data = $this->_attributes;
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
            $this->id = $res['_id'];
            return true;
        } else {
            return false;
        }
    }

    public function update()
    {
        $data = $this->_attributes;
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'doc' => $data
            ]
        ];
        $params['id'] = $this->_old_attributes['id'];
        return $this->client->update($this->combineParams($params));
    }

    public function realDelete()
    {
        if ($this->id) {
            $params = [
                'index' => $this->index,
                'type' => $this->type,
                'id' => $this->id
            ];
            return $this->client->delete($this->combineParams($params));
        } else {
            return false;
        }
    }

    public static function findById($id)
    {
        $params = [
            'index' => static::indexName(),
            'type' => static::tableName(),
            'id' => $id
        ];
        $client = self::client();
        $res = $client->get(self::combineParams($params));
        $data = [];
        if ($res['found']) {
            $data = $res['_source'];
        }
        if ($data) {
            $_this = new self();
            $_this->_is_new_record = false;
            $_this->setAttributes($data);
            $_this->setOldAttributes($data);
            return $_this;
        } else {
            return null;
        }
    }

    public static function findOne($cond = [], $order = ['id' => 'desc'])
    {
        $resp_data = self::find($cond, $order, 1, 1);
        $total = $resp_data['total'];
        unset($resp_data['total']);
        if ($total == 0) {
            return null;
        } else {
            $_this = new self();
            $_this->_is_new_record = false;
            $_this->setAttributes($resp_data[0]);
            $_this->setOldAttributes($resp_data[0]);
            return $_this;
        }
    }

    public static function count($cond = [])
    {
        $resp_data = self::find($cond, ['id' => 'desc'], 1, 1);
        return $resp_data['total'];
    }

    public static function findAll($cond = [], $order = ['id' => 'desc'])
    {
        //可以传的最大值为10000
        $resp_data = self::find($cond, $order, 1, 10000);
        $total = $resp_data['total'];
        unset($resp_data['total']);
        if ($total == 0) {
            return null;
        } else {
            $datas = [];
            foreach ($resp_data as $data) {
                $_this = new self();
                $_this->_is_new_record = false;
                $_this->setAttributes($data);
                $_this->setOldAttributes($data);
                $datas[] = $_this;
            }
            return $datas;
        }
    }

    public static function findPagination($cond = [], $order = ['id' => 'desc'], $page = 1, $per_page = 10)
    {
        $resp_data = self::find($cond, $order, $page, $per_page);
        $total = $resp_data['total'];
        unset($resp_data['total']);
        if ($total == 0) {
            return null;
        } else {
            $datas = [];
            foreach ($resp_data as $data) {
                $_this = new self();
                $_this->_is_new_record = false;
                $_this->setAttributes($data);
                $_this->setOldAttributes($data);
                $datas[] = $_this;
            }
            return $datas;
        }
    }


    /**
     * $condition ['age' => ['gt'=>10,'lt'=>20]]
     */
    static function find($condition = [], $order = ['id' => 'desc'], $page = 1, $per_page = 10000)
    {
        $from = ($page - 1) * $per_page;
        $params = [
            'index' => self::indexName(),
            'type' => self::tableName(),
            'from' => $from,
            'size' => $per_page
        ];

        $client = self::client();

        //排序处理
        if ($order) {
            $sort = [];
            foreach ($order as $field => $esc) {
                $sort[] = [$field => ["order" => $esc]];
            }
            $params['body']['sort'] = $sort;
        }

        if ($query = self::getConditions($condition)) {
            $params['body']['query'] = $query;
        }

        //显示字段设置
        $temp_fields = static::$attributes;
        $fields = [];
        foreach ($temp_fields as $field_name) {
            $fields[$field_name] = new \stdClass();
        }
        $params['body'] = [
            '_source' => ["include" => $temp_fields],
            'highlight' => [
                'pre_tags' => ['<span style="color:red">'],
                'post_tags' => ['</span>'],
                'fields' => $fields
            ]
        ];

        $res = $client->search(self::combineParams($params));
        return self::getData($res);
    }

    /**
     * 过滤统计
     * 获取聚合值 min,max,sum,avg
     * $aggs = [['stats'=>'age'],['max'=>'age'],[ 'range' => ['age'=>[ ['to'=>30],['from'=>10],['from'=>10,'to'=>30] ] ]]]
     */
    public static function aggs($aggs, $group = '', $condition = [])
    {
        $params = [
            'index' => self::indexName(),
            'type' => self::tableName(),
        ];

        if ($query = self::getConditions($condition)) {
            $params['body']['query'] = $query;
        }

        //group 分组条件  简单分组  不指定size默认就显示10个分组数据
        if ($group) {
            $params['body']['aggs']['terms_' . $group]['terms'] = ['field' => $group, 'size' => 10000];
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

        $client = self::client();

        $data = [];
        $res = $client->search(self::combineParams($params));
        if (isset($res['error'])) {
            $res['error']['status'] = $res['status'];
            return $res['error'];
        }
        //返回统计结果
        if (isset($res['aggregations'])) {
            $data = $res['aggregations'];
        }
        return $data;
    }

    protected static function getConditions($condition, $type = 'match')
    {
        $query = [];
        foreach ($condition as $field => $value) {
            if (is_array($value)) {
                $query['range'] = [$field => $value];
                unset($condition[$field]);
            } else {
                if ($type == 'match') {
                    $query['bool']['must'][] = ['match' => ["$field" => $value]];
                }

                if ($type == 'filter') {
                    $query['bool']['filter'][] = ['term' => ["$field" => $value]];
                }
            }
        }
        return $query;
    }

    protected static function getData($res)
    {
        $data = [];

        if (isset($res['error'])) {
            $res['error']['status'] = $res['status'];
            return $res['error'];
        }

        $data['total'] = $res['hits']['total'];
        if (isset($res['hits'])) {
            foreach ($res['hits']['hits'] as $v) {
                $v['_source']['_id'] = $v['_id'];

                foreach ($v['highlight'] as $field => $value) {
                    $v['_source']['_' . $field] = $value[0];
                }

                $data[] = $v['_source'];
            }
        }
        return $data;
    }

    protected static function combineParams($params)
    {
        $params = array_merge($params, self::$ignore_params);
        return $params;
    }
}