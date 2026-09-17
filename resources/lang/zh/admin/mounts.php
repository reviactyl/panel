<?php

return [

    'label' => '挂载',
    'plural_label' => '挂载',

    'sections' => [
        'configuration' => '挂载配置',
    ],

    'fields' => [
        'name' => '名称',
        'description' => '描述',
        'source' => '来源路径',
        'target' => '目标路径',
        'read_only' => '只读',
        'user_mountable' => '允许用户挂载',
    ],

    'helpers' => [
        'name' => '用于区分此挂载与其他挂载的唯一名称。',
        'description' => '便于人工阅读的此挂载的详细说明。',
        'source' => '主机上要挂载到容器中的文件路径。',
        'target' => '容器内部将此路径挂载到的目标位置。',
        'read_only' => '如果勾选，则容器内部此挂载点将为只读。',
        'user_mountable' => '如果勾选，用户将可以在自己的服务器中挂载此挂载点。',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => '名称',
        'source' => '源路径',
        'target' => '目标',
        'read_only' => '只读',
        'user_mountable' => '允许用户挂载',
    ],

    'actions' => [
        'attach_egg' => '附上鸡蛋',
        'attach_node' => '附加节点',
    ],

];
