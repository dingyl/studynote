<?php
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

$res = $client->indices()->create($params);   //创建库索引

