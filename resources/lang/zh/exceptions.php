<?php

return [
    'daemon_connection_failed' => '尝试与守护进程通信时发生异常，导致 HTTP/:code 响应代码。此异常已被记录。',
    'node' => [
        'servers_attached' => '节点必须没有任何关联的服务器才能被删除。',
        'daemon_off_config_updated' => '守护进程配置已更新，但在尝试自动更新守护进程上的配置文件时遇到错误。您需要手动更新守护进程的配置文件（config.yml）以使这些更改生效。',
    ],
    'allocations' => [
        'server_using' => '目前有服务器分配到此分配。只有在没有服务器分配时才能删除分配。',
        'too_many_ports' => '不支持一次在单个范围内添加超过 1000 个端口。',
        'invalid_mapping' => '为 :port 提供的映射无效，无法处理。',
        'cidr_out_of_range' => 'CIDR 表示法只允许 /25 到 /32 之间的掩码。',
        'port_out_of_range' => '分配中的端口必须大于 1024 且小于或等于 65535。',
    ],
    'nest' => [
        'delete_has_servers' => '无法从面板中删除有活动服务器附加的巢穴。',
        'egg' => [
            'delete_has_servers' => '无法从面板中删除有活动服务器附加的 Egg。',
            'invalid_copy_id' => '选择用于复制脚本的 Egg 不存在，或者它本身正在复制脚本。',
            'must_be_child' => '此 Egg 的"从...复制设置"指令必须是所选巢穴的子选项。',
            'has_children' => '此 Egg 是一个或多个其他 Eggs 的父级。请先删除那些 Eggs 再删除此 Egg。',
        ],
        'variables' => [
            'env_not_unique' => '环境变量 :name 对于此 Egg 必须是唯一的。',
            'reserved_name' => '环境变量 :name 受保护，不能分配给变量。',
            'bad_validation_rule' => '验证规则 ":rule" 对于此应用程序不是有效规则。',
        ],
        'importer' => [
            'json_error' => '尝试解析 JSON 文件时出错：:error。',
            'file_error' => '提供的 JSON 文件无效。',
            'invalid_json_provided' => '提供的 JSON 文件格式无法识别。',
        ],
    ],
    'subusers' => [
        'editing_self' => '不允许编辑您自己的子用户帐户。',
        'user_is_owner' => '您不能将服务器所有者添加为此服务器的子用户。',
        'subuser_exists' => '使用该电子邮件地址的用户已被分配为此服务器的子用户。',
    ],
    'databases' => [
        'delete_has_databases' => '无法删除有活动数据库关联的数据库主机服务器。',
    ],
    'tasks' => [
        'chain_interval_too_long' => '链式任务的最大间隔时间为 15 分钟。',
    ],
    'locations' => [
        'has_nodes' => '无法删除有活动节点附加的位置。',
    ],
    'users' => [
        'node_revocation_failed' => '在 <a href=":link">节点 #:node</a> 上撤销密钥失败。:error',
    ],
    'deployment' => [
        'no_viable_nodes' => '找不到满足自动部署指定要求的节点。',
        'no_viable_allocations' => '找不到满足自动部署要求的分配。',
    ],
    'api' => [
        'resource_not_found' => '请求的资源在此服务器上不存在。',
    ],
    'social' => [
        'unlink_only_login' => '在未先设置密码的情况下，无法解除你唯一的登录方式。',
    ],
];
