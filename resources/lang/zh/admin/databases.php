<?php

return [

    'label' => '数据库',
    'plural-label' => '数据库',

    'none' => '无',

    'sections' => [
        'host_details' => [
            'title' => '主机信息',
            'description' => '配置数据库主机连接设置。',
        ],

        'authentication' => [
            'title' => '身份验证',
        ],

        'linked_node' => [
            'title' => '关联节点',
        ],
    ],

    'placeholders' => [
        'name' => '生产环境 MySQL',
    ],

    'helpers' => [
        'host' => '数据库服务器的主机名或 IP 地址。',
        'linked_node' => '可选。将此主机关联到指定节点。',
    ],

    'fields' => [
        'linked_node' => '关联节点',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => '名称',
        'host' => '主机',
        'port' => '端口',
        'username' => '用户名',
        'linked_node' => '关联节点',
        'databases' => '数据库',
        'created' => '创建时间',
    ],

    'actions' => [
        'edit' => '编辑',
        'delete' => '删除',
    ],

    'errors' => [
        'cannot_delete' => '无法删除仍有关联数据库的数据库主机。',
    ],

];
