<?php

return [
    'label' => '角色',
    'plural-label' => '角色',
    'all' => '全部',
    'select_all' => '全选',
    'deselect_all' => '取消全选',
    'details' => [
        'title' => '角色详情',
        'description' => '角色决定用户在面板中可以做什么，包括管理后台以及不属于他们的服务器。',
        'name' => '名称',
        'name_placeholder' => '支持团队',
        'description_label' => '描述',
        'description_placeholder' => '这个角色的用途。',
    ],
    'table' => [
        'name' => '名称',
        'permissions' => '权限',
        'users' => '用户',
        'updated' => '最后更新',
    ],
    'groups' => [
        'management' => [
            'title' => '管理',
            'description' => '服务器、使用服务器的人以及服务器所运行的基础设施。',
        ],
        'service' => [
            'title' => '服务管理',
            'description' => '服务器上可以安装什么，以及面板本身如何扩展和更新。',
        ],
        'administration' => [
            'title' => '管理',
            'description' => '面板范围的配置、外观和概览。',
        ],
    ],
    'resources' => [
        'servers' => '服务器',
        'users' => '用户',
        'roles' => '角色',
        'nodes' => '节点',
        'locations' => '区域',
        'database_hosts' => '数据库主机',
        'nests' => '预设',
        'eggs' => 'Egg',
        'mounts' => '挂载',
        'alerts' => '公告',
        'webhooks' => 'Webhook',
        'extensions' => '扩展',
        'activity_log' => '活动日志',
        'monitoring' => '监控',
        'settings' => '设置',
        'designify' => 'Designify',
        'updates' => '软件更新',
    ],
    'actions' => [
        'send_email' => '发送邮件',
        'read' => '查看',
        'create' => '创建',
        'update' => '编辑',
        'delete' => '删除',
        'assign' => '分配给用户',
        'manage' => '管理',
        'install' => '安装更新',
    ],
    'messages' => [
        'in_use' => '此角色仍分配给用户。请先从他们身上移除再删除。',
    ],
    'user' => [
        'roles' => '角色',
        'roles_self' => '你不能更改自己的角色。',
        'none' => '无',
    ],
];
