<?php

return [
    'label' => '服务器',
    'plural-label' => '服务器',

    'sections' => [
        'identity' => [
            'title' => '身份信息',
            'description' => '基本服务器信息及所有权。',
        ],
        'allocation' => [
            'title' => '分配',
            'description' => '为该服务器选择节点和网络分配。',
        ],
        'startup' => [
            'title' => '启动',
            'description' => '配置预设、启动命令以及 Docker 镜像。',
        ],
        'resources' => [
            'title' => '资源限制',
            'description' => '定义服务器的资源限制。',
        ],
        'feature_limits' => [
            'title' => '功能限制',
            'description' => '限制数据库、端口分配和备份数量。',
        ],
        'environment' => [
            'title' => '环境变量',
            'description' => '为所选预设设置环境变量值。',
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => '高级模式',
            'helper' => '切换以显示额外的服务器配置选项。仅在您了解这些额外设置的影响时才启用。',
        ],
        'external_id' => [
            'label' => '外部 ID',
            'helper' => '此服务器的可选唯一标识符。',
        ],
        'owner' => [
            'label' => '所有者',
            'helper' => '选择拥有此服务器的用户。',
        ],
        'name' => [
            'label' => '名称',
            'placeholder' => '服务器名称',
            'helper' => '服务器的简短名称。',
        ],
        'description' => [
            'label' => '描述',
            'placeholder' => '服务器描述',
            'helper' => '此服务器的可选描述。',
        ],
        'node' => [
            'label' => '节点',
            'helper' => '此服务器将部署到的节点。',
        ],
        'allocation' => [
            'label' => '主要分配',
            'helper' => '此服务器的默认 IP/端口分配。',
        ],
        'additional_allocations' => [
            'label' => '额外分配',
            'helper' => '可选的额外分配端口。',
        ],
        'nest' => [
            'label' => '预设组',
            'helper' => '此服务器所属的服务预设组。',
        ],
        'egg' => [
            'label' => '预设',
            'helper' => '定义服务器行为的预设。',
        ],
        'startup' => [
            'label' => '启动命令',
            'helper' => '服务器的启动命令。',
        ],
        'image' => [
            'label' => 'Docker 镜像',
            'placeholder' => '例如：ghcr.io/pterodactyl/yolks:java_17',
            'helper' => '用于运行此服务器的 Docker 镜像。',
            'custom' => '自定义',
        ],
        'skip_scripts' => [
            'label' => '跳过预设脚本',
            'helper' => '跳过预设安装脚本。',
        ],
        'start_on_completion' => [
            'label' => '创建完成后启动',
            'helper' => '安装完成后自动启动服务器。',
        ],
        'memory' => [
            'label' => '内存',
            'helper' => '总内存分配量。设置为 0 表示无限制。（由于启动命令限制，无限内存不适用于 Minecraft 预设）',
        ],
        'swap' => [
            'label' => '交换分区',
            'helper' => '交换内存分配量。设置为 0 禁用交换分区，或设置为 -1 允许无限交换。',
        ],
        'disk' => [
            'label' => '磁盘',
            'helper' => '磁盘空间分配量。设置为 0 表示无限制。',
        ],
        'io' => [
            'label' => 'IO 权重',
            'helper' => '相对磁盘 I/O 优先级（10-1000）。',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'CPU 限制（百分比）。100% 表示占用 1 个完整核心，200% 表示占用 2 个完整核心，以此类推。',
        ],
        'enter_size_in_gib' => [
            'label' => '输入大小（GiB）',
            'helper' => '您可以使用 "GiB" 后缀输入大小（例如 10GiB = 10240MiB）。',
        ],
        'threads' => [
            'label' => 'CPU 线程',
            'helper' => '可选的线程绑定。例如：0-1,3。',
        ],
        'oom_disabled' => [
            'label' => '禁用 OOM Killer',
            'helper' => '防止内核在内存不足时杀死进程。',
        ],
        'database_limit' => [
            'label' => '数据库限制',
            'helper' => '最大数据库数量。',
        ],
        'allocation_limit' => [
            'label' => '端口分配限制',
            'helper' => '最大端口分配数量。',
        ],
        'backup_limit' => [
            'label' => '备份限制',
            'helper' => '最大备份数量。',
        ],
        'environment' => [
            'key' => '变量',
            'value' => '值',
            'helper' => '该预设的环境变量。',
        ],
        'use_custom_image' => [
            'label' => '使用自定义镜像',
            'helper' => '启用后，将使用自定义 Docker 镜像，而非预设自带镜像。',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => '名称',
        'owner' => '所有者',
        'node' => '节点',
        'allocation' => '分配',
        'status' => '状态',
        'egg' => '预设',
        'memory' => '内存',
        'disk' => '磁盘',
        'cpu' => 'CPU',
        'created' => '创建时间',
        'updated' => '更新时间',
        'installed' => '已安装',
        'no_status' => '无状态',
    ],

    'messages' => [
        'created' => '服务器已成功创建。',
        'updated' => '服务器已成功更新。',
        'deleted' => '服务器已成功删除。',
    ],

    'actions' => [
        'edit' => '编辑',
        'toggle_install_status' => '切换安装状态',
        'suspend' => '暂停',
        'unsuspend' => '解除暂停',
        'suspended' => '已暂停',
        'unsuspended' => '已解除暂停',
        'reinstall' => '重新安装',
        'delete' => '删除',
        'delete_forcibly' => '强制删除',
        'view' => '查看',
    ],

    'exceptions' => [
        'no_new_default_allocation' => '您正在尝试删除此服务器的默认分配，但没有可用的备用分配。',
        'marked_as_failed' => '此服务器在之前的安装中被标记为失败。在此状态下无法切换当前状态。',
        'bad_variable' => ':name 变量存在验证错误。',
        'daemon_exception' => '尝试与守护进程通信时发生异常，导致 HTTP/:code 响应代码。此异常已被记录。（请求 ID：:request_id）',
        'default_allocation_not_found' => '在此服务器的分配中找不到所请求的默认分配。',
    ],

    'alerts' => [
        'install_toggled' => '服务器安装状态已切换。',
        'server_suspended' => '服务器已成功执行 :action 。',
        'server_reinstalled' => '服务器重装任务已开始。',
        'server_deleted' => '服务器已删除。',
        'server_delete_failed' => '服务器删除失败。',
        'startup_changed' => '此服务器的启动配置已更新。如果此服务器的巢穴或 egg 已更改，将立即进行重新安装。',
        'server_created' => '服务器已成功在面板上创建。请给守护进程几分钟时间来完全安装此服务器。',
        'build_updated' => '此服务器的构建详情已更新。某些更改可能需要重新启动才能生效。',
        'suspension_toggled' => '服务器暂停状态已更改为 :status。',
        'rebuild_on_boot' => '此服务器已被标记为需要重建 Docker 容器。这将在下次启动服务器时发生。',
        'details_updated' => '服务器详情已成功更新。',
        'docker_image_updated' => '已成功更改用于此服务器的默认 Docker 镜像。需要重新启动才能应用此更改。',
        'node_required' => '在向此面板添加服务器之前，您必须至少配置一个节点。',
        'transfer_nodes_required' => '在传输服务器之前，您必须至少配置两个节点。',
        'transfer_started' => '服务器传输已开始。',
        'transfer_not_viable' => '您选择的节点没有足够的磁盘空间或内存来容纳此服务器。',
    ],
];
