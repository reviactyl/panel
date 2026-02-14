<?php

return [
    'label' => '节点',
    'plural-label' => '节点',

    'sections' => [
        'identity' => [
            'title' => '身份信息',
            'description' => '基本节点信息。',
        ],
        'connection' => [
            'title' => '连接详情',
            'description' => '配置如何连接到此节点。',
        ],
        'resources' => [
            'title' => '资源分配',
            'description' => '为该节点定义内存和磁盘限制。',
        ],
        'daemon' => [
            'title' => '守护进程配置',
            'description' => '配置守护进程特定设置。',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => '公开',
            'helper' => '将节点设置为私有将拒绝自动部署到此节点的能力。 ',
        ],
        'name' => [
            'label' => '名称',
            'placeholder' => '节点名称',
            'helper' => '为此节点设置一个具有描述性的名称。',
        ],
        'description' => [
            'label' => '描述',
            'placeholder' => '节点描述',
            'helper' => '此节点的可选描述。',
        ],
        'location' => [
            'label' => '区域',
            'helper' => '此节点所属的区域。',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => '节点的完整限定域名或 IP 地址。',
        ],
        'ssl' => [
            'label' => '使用 SSL',
            'helper' => '节点上的守护进程是否配置为使用 SSL 进行安全通信。',
            'helper_forced' => '此面板正在通过 HTTPS 运行，因此强制为此节点启用 SSL。',
        ],
        'behind_proxy' => [
            'label' => '位于代理后',
            'helper' => '如果此节点位于 Cloudflare 等代理后面，请启用此选项。',
        ],
        'maintenance_mode' => [
            'label' => '维护模式',
            'helper' => '阻止在此节点上创建新的服务器实例。',
        ],
        'memory' => [
            'label' => '总内存',
            'helper' => '节点上可用的总内存（以 MiB 为单位）。',
        ],
        'memory_overallocate' => [
            'label' => '内存超分配',
            'helper' => '内存超分配百分比。使用 -1 禁用检查。',
        ],
        'disk' => [
            'label' => '总磁盘空间',
            'helper' => '节点上可用的总磁盘空间（以 MiB 为单位）。',
        ],
        'disk_overallocate' => [
            'label' => '磁盘超分配',
            'helper' => '磁盘超分配百分比。使用 -1 禁用检查。',
        ],
        'upload_size' => [
            'label' => '最大上传大小',
            'helper' => '通过 Web 面板允许的最大文件上传大小。',
        ],
        'daemon_base' => [
            'label' => '基础目录',
            'placeholder' => '/home/daemon-files',
            'helper' => '服务器文件存储的目录。',
        ],
        'daemon_listen' => [
            'label' => '守护进程端口',
            'helper' => '守护进程用于 HTTP 通信的监听端口。',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP 端口',
            'helper' => '用于 SFTP 连接的端口。',
        ],
        'daemon_token_id' => [
            'label' => '令牌 ID',
        ],
        'container_text' => [
            'label' => '容器前缀',
            'helper' => '显示在容器名称中的文本前缀。',
        ],
        'daemon_text' => [
            'label' => '守护进程前缀',
            'helper' => '显示在守护进程日志中的文本前缀。',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => '名称',
        'location' => '区域',
        'fqdn' => 'FQDN',
        'scheme' => '协议',
        'public' => '公开',
        'behind_proxy' => '位于代理后',
        'maintenance_mode' => '维护',
        'memory' => '内存',
        'memory_overallocate' => '内存超分配',
        'disk' => '磁盘',
        'disk_overallocate' => '磁盘超分配',
        'upload_size' => '上传大小',
        'daemon_listen' => '守护进程端口',
        'daemon_sftp' => 'SFTP 端口',
        'daemon_base' => '基础目录',
        'servers' => '服务器',
        'created' => '创建时间',
        'updated' => '更新时间',
    ],

    'actions' => [
        'create' => '创建',
        'edit' => '编辑',
        'delete' => '删除',
        'view' => '查看',
    ],

    'messages' => [
        'created' => '节点已成功创建。',
        'updated' => '节点已成功更新。',
        'deleted' => '节点已成功删除。',
        'cannot_delete_with_servers' => '无法删除存在活跃服务器的节点。',
    ],

    'allocations' => [
        'label' => '分配',
        'table' => [
            'ip' => 'IP',
            'port' => '端口',
            'alias' => '别名',
            'server' => '服务器',
            'notes' => '备注',
            'created' => '创建时间',
            'unassigned' => '未分配',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP 地址',
                'helper' => '支持单个 IP 或 CIDR（例如 192.168.0.1 或 192.168.0.0/24）。',
            ],
            'allocation_ports' => [
                'label' => '端口',
                'helper' => '输入端口或端口范围（例如 25565, 25566, 25570-25580）。',
            ],
            'allocation_alias' => [
                'label' => 'IP 别名',
                'helper' => '可选的别名，用于代替 IP 显示。',
            ],
        ],
        'actions' => [
            'add' => '添加分配',
            'delete' => '删除',
        ],
        'messages' => [
            'created' => '已添加分配。',
            'deleted' => '已删除分配。',
            'failed' => '分配操作失败。',
        ],
    ],
    
    'validation' => [
        'fqdn_not_resolvable' => '提供的 FQDN 或 IP 地址无法解析为有效的 IP 地址。',
        'fqdn_required_for_ssl' => '要为此节点使用 SSL，需要一个能解析为公共 IP 地址的完全限定域名。',
    ],
    'notices' => [
        'allocations_added' => '分配已成功添加到此节点。',
        'node_deleted' => '节点已成功从面板中删除。',
        'location_required' => '在向此面板添加节点之前，您必须至少配置一个位置。',
        'node_created' => '新节点创建成功。您可以通过访问"配置"选项卡自动配置此机器上的守护进程。<strong>在添加任何服务器之前，您必须首先分配至少一个 IP 地址和端口。</strong>',
        'node_updated' => '节点信息已更新。如果更改了任何守护进程设置，您需要重新启动它才能使这些更改生效。',
        'unallocated_deleted' => '已删除 <code>:ip</code> 的所有未分配端口。',
    ],
];
