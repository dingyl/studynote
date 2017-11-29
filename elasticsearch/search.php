<?php
require '../functions.php';
require 'vendor/autoload.php';
$elastic = $client = Elasticsearch\ClientBuilder::create()->setHosts(['localhost:9200'])->build();
/**
 * 创建库索引的mapping结构
 */
$params = [
    'index' => 'my_index',  //索引名（相当于mysql的数据库）
    'body' => [
        'settings' => [
            'number_of_shards' => 5,  #分片数
        ],
        'mappings' => [
            'my_type' => [ //类型名（相当于mysql的表）
                '_all' => [
                    'enabled' => 'false'
                ],
                '_routing' => [
                    'required' => 'true'
                ],
                'properties' => [ //文档类型设置（相当于mysql的数据类型）
                    'name' => [
                        'type' => 'string',
                        'store' => 'true'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ]
    ]
];

//$res = $client->indices()->create($params);   //创建库索引

/**
 * 库索引操作
 */
$params = [
    'index' => 'my_index',
    'client' => [
        'ignore' => 404
    ]
];
//获取索引信息
//$res = $client->indices()->getSettings($params);
//判断索引是否存在
//$res = $client->indices()->exists($params);

//删除索引
//$res = $client->indices()->delete($params);

////获取mapping信息
//$res = $client->indices()->getMapping($params);


/**
 * 文档操作
 */

//添加文档



//查询文档


//更新文档

//删除文档
p($res);

