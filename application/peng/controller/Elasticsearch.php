<?php

namespace app\peng\controller;
use Elasticsearch\ClientBuilder;
//参考文档：https://www.elastic.co/guide/cn/elasticsearch/php/current/_quickstart.html#_%E8%8E%B7%E5%8F%96%E4%B8%80%E4%B8%AA%E6%96%87%E6%A1%A3
class Elasticsearch extends Base
{
    public function index() {
        $host=['127.0.0.1:9200'];
        $client = ClientBuilder::create()->setHosts($host)->build();
        //索引一个文档--------------------------------------------------------------
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id',
//            'body' => ['testField' => 'abc']
//        ];
//        $response = $client->index($params);
//        print_r($response);die;

        //获取一个文档--------------------------------------------------------------
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id'
//        ];
//
//        $response = $client->get($params);
//        print_r($response);die;

        //搜索一个文档--------------------------------------------------------------
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'body' => [
//                'query' => [
//                    'match' => [
//                        'testField' => 'abc'
//                    ]
//                ]
//            ]
//        ];
//
//        $response = $client->search($params);
//        print_r($response);

        //删除一个文档
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id'
//        ];
//
//        $response = $client->delete($params);
//        print_r($response);

        //删除一个索引
//        $deleteParams = [
//            'index' => 'my_index'
//        ];
//        $response = $client->indices()->delete($deleteParams);
//        print_r($response);

        //创建一个索引
        $params = [
            'index' => 'my_index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        $response = $client->indices()->create($params);
        print_r($response);
    }
}