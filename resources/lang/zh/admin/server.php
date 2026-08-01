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

    'status' => [
        'online' => '在线的',
        'offline' => '离线',
        'starting' => '开始',
        'stopping' => '停止',
        'crashed' => '坠毁',
        'installing' => '安装中',
        'restoring_backup' => '恢复备份',
        'install_failed' => '安装失败',
        'reinstall_failed' => '重新安装失败',
        'suspended' => '暂停',
    ],

    'create' => [
        'sections' => [
            'core_details' => '核心细节',
            'allocation' => '分配管理',
            'feature_limits' => '应用程序功能限制',
            'resources' => '资源管理',
            'nest' => '嵌套配置',
            'docker' => 'Docker 配置',
            'startup' => '启动配置',
            'variables' => '服务变量',
        ],

        'fields' => [
            'name' => [
                'label' => '服务器名称',
                'placeholder' => '服务器名称',
                'helper' => '字符限制：a-z A-Z 0-9 _ - 。和空间。',
            ],
            'owner' => [
                'label' => '服务器所有者',
                'helper' => '服务器所有者的电子邮件地址。',
            ],
            'description' => [
                'label' => '服务器描述',
                'helper' => '该服务器的简要描述。',
            ],
            'start_on_completion' => [
                'label' => '安装后启动服务器',
            ],
            'node' => [
                'label' => '节点',
                'helper' => '该服务器将部署到的节点。',
            ],
            'allocation' => [
                'label' => '默认分配',
                'helper' => '将分配给该服务器的主要分配。',
            ],
            'additional_allocations' => [
                'label' => '额外分配',
                'helper' => '创建时分配给该服务器的附加分配。',
            ],
            'database_limit' => [
                'label' => '数据库限制',
                'helper' => '允许用户为此服务器创建的数据库总数。',
            ],
            'allocation_limit' => [
                'label' => '分配限额',
                'helper' => '允许用户为此服务器创建的分配总数。',
            ],
            'backup_limit' => [
                'label' => '备份限制',
                'helper' => '可以为此服务器创建的备份总数。',
            ],
            'cpu' => [
                'label' => 'CPU限制',
                'helper' => '设置 0 表示没有 CPU 限制。完整的虚拟核心是 100%。',
            ],
            'threads' => [
                'label' => 'CPU固定',
                'helper' => '高级：使用单个数字或逗号分隔列表，例如 0、0-1,3 或 0,1,3,4。',
            ],
            'memory' => [
                'label' => '记忆',
                'helper' => '该容器允许的最大内存量。设置 0 表示无限制。',
            ],
            'swap' => [
                'label' => '交换',
                'helper' => '设置 0 禁用交换，或设置 -1 允许无限制交换。',
            ],
            'disk' => [
                'label' => '磁盘空间',
                'helper' => '设置 0 以允许无限制的磁盘使用。',
            ],
            'io' => [
                'label' => '块 IO 重量',
                'helper' => '高级：相对于其他正在运行的容器的 IO 性能。值应为 10 到 1000。',
            ],
            'oom_disabled' => [
                'label' => '启用 OOM Killer',
                'helper' => '如果服务器超出内存限制，则终止服务器。',
            ],
            'nest' => [
                'label' => '巢',
                'helper' => '选择该服务器将分组到的 Nest。',
            ],
            'egg' => [
                'label' => '蛋',
                'helper' => '选择将定义该服务器如何运行的蛋。',
            ],
            'skip_scripts' => [
                'label' => '跳过 Egg 安装脚本',
                'helper' => '如果所选 Egg 附加有安装脚本，则该脚本将在安装期间运行，除非选中此项。',
            ],
            'image' => [
                'label' => 'Docker 镜像',
                'helper' => '从下拉列表中选择图像，或在下面输入自定义图像。',
            ],
            'custom_image' => [
                'label' => '自定义 Docker 镜像',
                'placeholder' => '或者输入自定义图像...',
                'helper' => '这是将用于运行该服务器的默认 Docker 映像。',
            ],
            'startup' => [
                'label' => '启动命令',
                'helper' => '可用的替代项：{{SERVER_MEMORY}}、{{SERVER_IP}} 和 {{SERVER_PORT}}。',
            ],
            'environment_placeholder' => [
                'label' => '选择一个egg来配置服务变量',
            ],
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
        'unlimited' => '无限',
    ],

    'messages' => [
        'created' => '服务器已成功创建。',
        'updated' => '服务器已成功更新。',
        'deleted' => '服务器已成功删除。',
    ],

    'actions' => [
        'edit' => '编辑',
        'random' => '随机的',
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
        'primary_allocation_updated' => '主要分配已更新。',
        'database_created' => '数据库已创建。',
        'database_password_reset' => '数据库密码重置。',
        'database_deleted' => '数据库已删除。',
    ],

    'edit' => [
        'tabs' => [
            'information' => '信息',
            'build_configuration' => '构建配置',
            'startup' => '启动',
            'manage' => '管理',
        ],

        'sections' => [
            'resource_management' => '资源管理',
            'application_feature_limits' => '应用程序功能限制',
            'allocation_management' => '分配管理',
            'startup_command_modification' => '启动命令修改',
            'service_configuration' => '服务配置',
            'docker_image_configuration' => 'Docker 镜像配置',
            'service_variables' => '服务变量',
            'reinstall_server' => '重新安装服务器',
            'install_status' => '安装状态',
            'suspend_server' => '暂停服务器',
            'unsuspend_server' => '取消暂停服务器',
            'transfer_server' => '传输服务器',
            'delete_server' => '删除服务器',
        ],

        'section_descriptions' => [
            'service_configuration' => '更改这些值可能会触发重新安装。服务器将立即停止执行该操作。',
            'reinstall_server' => '这将使用分配的服务脚本重新安装服务器。这可能会覆盖服务器数据。',
            'install_status' => '将安装状态从“已卸载”更改为“已安装”，反之亦然。',
            'suspend_server' => '这将停止正在运行的进程并阻止用户通过面板或 API 管理服务器。',
            'unsuspend_server' => '这将取消暂停服务器并恢复正常的用户访问。',
            'transfer_server_transferring' => '该服务器当前正在转移到另一个节点。',
            'transfer_server' => '将此服务器转移到连接到此面板的另一个节点。',
            'delete_server' => '这将从面板和代理中永久删除服务器。如有必要，强制删除会跳过代理删除。',
        ],

        'fields' => [
            'server_name' => [
                'label' => '服务器名称',
                'helper' => '字符限制：a-zA-Z0-9_-、空格和标准可打印字符。',
            ],
            'server_owner' => [
                'label' => '服务器所有者',
                'helper' => '更改所有权会自动撤销前所有者的守护程序令牌。',
            ],
            'server_description' => [
                'label' => '服务器描述',
                'helper' => '该服务器的简要描述。',
            ],
            'server_uuid' => [
                'label' => '服务器UUID',
            ],
            'server_uuid_short' => [
                'label' => '服务器 UUID（短）',
            ],
            'external_identifier' => [
                'label' => '外部标识符',
                'helper' => '留空则不分配外部标识符。外部 ID 对于该服务器应该是唯一的。',
            ],
            'game_port' => [
                'label' => '游戏端口',
                'helper' => '该游戏服务器将使用的默认连接地址。',
            ],
            'additional_ports' => [
                'label' => '附加端口',
                'helper' => '分配或删除额外的端口。不同IP上的相同端口不能分配给同一台服务器。',
            ],
            'startup_command' => [
                'label' => '启动命令',
                'helper' => '默认可用：{{SERVER_MEMORY}}、{{SERVER_IP}} 和 {{SERVER_PORT}}。',
            ],
            'default_startup_command' => [
                'label' => '默认启动命令',
                'error' => '错误：启动未定义！',
            ],
            'cpu_limit' => [
                'label' => 'CPU限制',
                'helper' => '每个虚拟核心都是100%。设置 0 表示不受限制的 CPU 时间。',
            ],
            'cpu_pinning' => [
                'label' => 'CPU固定',
                'helper' => '高级：为所有核心留空。示例：0、0-1,3 或 0,1,3,4。',
            ],
            'allocated_memory' => [
                'label' => '分配内存',
                'helper' => '该容器允许的最大内存量。设置 0 表示无限制。',
            ],
            'allocated_swap' => [
                'label' => '分配掉期',
                'helper' => '设置 0 禁用交换，或设置 -1 允许无限制交换。',
            ],
            'disk_space_limit' => [
                'label' => '磁盘空间限制',
                'helper' => '设置 0 以允许无限制的磁盘使用。',
            ],
            'block_io_proportion' => [
                'label' => '块IO比例',
                'helper' => '高级：相对于其他正在运行的容器的 IO 性能。值应为 10 到 1000。',
            ],
            'disable_oom_killer' => [
                'label' => '禁用 OOM Killer',
                'helper' => '启用 OOM Killer 可能会导致服务器进程意外退出。',
            ],
            'database_limit' => [
                'label' => '数据库限制',
                'helper' => '允许用户为此服务器创建的数据库总数。',
            ],
            'allocation_limit' => [
                'label' => '分配限额',
                'helper' => '允许用户为此服务器创建的分配总数。',
            ],
            'backup_limit' => [
                'label' => '备份限制',
                'helper' => '可以为此服务器创建的备份总数。',
            ],
            'image' => [
                'label' => '图像',
                'helper' => '从下拉列表中选择图像，或在下面输入自定义图像。',
            ],
            'custom_image' => [
                'label' => '自定义图像',
                'placeholder' => '或者输入自定义图像...',
                'helper' => '这是将用于运行该服务器的 Docker 映像。',
            ],
            'transfer_node' => [
                'label' => '节点',
                'helper' => '该服务器将转移到的节点。',
            ],
            'transfer_allocation' => [
                'label' => '默认分配',
                'helper' => '将分配给该服务器的主要分配。',
            ],
            'transfer_additional_allocations' => [
                'label' => '额外分配',
                'helper' => '在传输时分配给该服务器的附加分配。',
            ],
        ],

        'actions' => [
            'reinstall_server' => '重新安装服务器',
            'toggle_install_status' => '切换安装状态',
            'suspend_server' => '暂停服务器',
            'unsuspend_server' => '取消暂停服务器',
            'transfer_server' => '传输服务器',
            'confirm' => '确认',
            'delete_server' => '删除服务器',
            'forcibly_delete_server' => '强制删除服务器',
        ],
    ],

    'allocations' => [
        'title' => '分配',

        'table' => [
            'ip' => '知识产权',
            'port' => '港口',
            'alias' => '别名',
            'primary' => '基本的',
            'notes' => '笔记',
            'created' => '已创建',
        ],

        'placeholder' => [
            'no_alias_assigned' => '未分配别名',
        ],

        'actions' => [
            'make_primary' => '设为主要',
        ],
    ],

    'databases' => [
        'title' => '数据库',

        'table' => [
            'database' => '数据库',
            'username' => '用户名',
            'remote' => '偏僻的',
            'host' => '主持人',
            'max_connections' => '最大连接数',
            'created' => '已创建',
        ],

        'placeholder' => [
            'unlimited' => '无限',
        ],

        'actions' => [
            'create_database' => '创建数据库',
            'reset_password' => '重置密码',
            'delete' => '删除',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => '数据库名称',
                'helper' => '该面板将使用服务器 ID 作为前缀，与旧的管理面板相匹配。',
            ],
            'database_host' => [
                'label' => '数据库主机',
            ],
            'remote' => [
                'label' => '偏僻的',
            ],
            'max_connections' => [
                'label' => '最大连接数',
            ],
        ],
    ],
];
