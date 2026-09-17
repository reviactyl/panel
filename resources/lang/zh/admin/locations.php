<?php

return [

    'label' => '区域',
    'plural-label' => '区域',

    'section' => [
        'title' => '区域详情',
        'description' => '定义一个可分配给节点的区域。',
    ],

    'fields' => [
        'short' => [
            'label' => '标识码',
            'helper' => '此位置的简短标识符。',
        ],

        'long' => [
            'label' => '描述',
            'helper' => '此位置的详细描述。',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => '标识码',
        'long' => '描述',
        'nodes' => '节点',
        'servers' => '服务器',
        'created' => '创建时间',
    ],

    'actions' => [
        'edit' => '编辑',
        'delete' => '删除',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => '无法删除仍有关联节点的区域。',
    ],

];
