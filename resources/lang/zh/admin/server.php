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
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Starting',
        'stopping' => 'Stopping',
        'crashed' => 'Crashed',
        'installing' => 'Installing',
        'restoring_backup' => 'Restoring Backup',
        'install_failed' => 'Install Failed',
        'reinstall_failed' => 'Reinstall Failed',
        'suspended' => 'Suspended',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Core Details',
            'allocation' => 'Allocation Management',
            'feature_limits' => 'Application Feature Limits',
            'resources' => 'Resource Management',
            'nest' => 'Nest Configuration',
            'docker' => 'Docker Configuration',
            'startup' => 'Startup Configuration',
            'variables' => 'Service Variables',
        ],

        'fields' => [
            'name' => [
                'label' => 'Server Name',
                'placeholder' => 'Server Name',
                'helper' => 'Character limits: a-z A-Z 0-9 _ - . and spaces.',
            ],
            'owner' => [
                'label' => 'Server Owner',
                'helper' => 'Email address of the Server Owner.',
            ],
            'description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'start_on_completion' => [
                'label' => 'Start Server when Installed',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be deployed to.',
            ],
            'allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on creation.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'cpu' => [
                'label' => 'CPU Limit',
                'helper' => 'Set 0 for no CPU limit. A full virtual core is 100%.',
            ],
            'threads' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: use a single number or comma separated list, for example 0, 0-1,3, or 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk' => [
                'label' => 'Disk Space',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Enable OOM Killer',
                'helper' => 'Terminates the server if it breaches memory limits.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Select the Nest that this server will be grouped under.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Select the Egg that will define how this server should operate.',
            ],
            'skip_scripts' => [
                'label' => 'Skip Egg Install Script',
                'helper' => 'If the selected Egg has an install script attached to it, the script will run during install unless this is checked.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Docker Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the default Docker image that will be used to run this server.',
            ],
            'startup' => [
                'label' => 'Startup Command',
                'helper' => 'Available substitutes: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Select an egg to configure service variables',
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
            'placeholder' => 'e.g. ghcr.io/pterodactyl/yolks:java_17',
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
        'unlimited' => 'Unlimited',
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
        'primary_allocation_updated' => 'Primary allocation updated.',
        'database_created' => 'Database created.',
        'database_password_reset' => 'Database password reset.',
        'database_deleted' => 'Database deleted.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build Configuration',
            'startup' => 'Startup',
            'manage' => 'Manage',
        ],

        'sections' => [
            'resource_management' => 'Resource Management',
            'application_feature_limits' => 'Application Feature Limits',
            'allocation_management' => 'Allocation Management',
            'startup_command_modification' => 'Startup Command Modification',
            'service_configuration' => 'Service Configuration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Service Variables',
            'reinstall_server' => 'Reinstall Server',
            'install_status' => 'Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'delete_server' => 'Delete Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Changing these values can trigger a reinstall. The server will be stopped immediately for that operation.',
            'reinstall_server' => 'This will reinstall the server with the assigned service scripts. This could overwrite server data.',
            'install_status' => 'Change install status from uninstalled to installed, or vice versa.',
            'suspend_server' => 'This will stop running processes and block the user from managing the server through the panel or API.',
            'unsuspend_server' => 'This will unsuspend the server and restore normal user access.',
            'transfer_server_transferring' => 'This server is currently being transferred to another node.',
            'transfer_server' => 'Transfer this server to another node connected to this panel.',
            'delete_server' => 'This permanently deletes the server from the panel and Agent. Force delete skips Agent deletion if necessary.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Server Name',
                'helper' => 'Character limits: a-zA-Z0-9_-, spaces, and standard printable characters.',
            ],
            'server_owner' => [
                'label' => 'Server Owner',
                'helper' => 'Changing ownership automatically revokes daemon tokens for the previous owner.',
            ],
            'server_description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (Short)',
            ],
            'external_identifier' => [
                'label' => 'External Identifier',
                'helper' => 'Leave empty to not assign an external identifier. The external ID should be unique to this server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'The default connection address that will be used for this game server.',
            ],
            'additional_ports' => [
                'label' => 'Additional Ports',
                'helper' => 'Assign or remove extra ports. Identical ports on different IPs cannot be assigned to the same server.',
            ],
            'startup_command' => [
                'label' => 'Startup Command',
                'helper' => 'Available by default: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Default Startup Command',
                'error' => 'ERROR: Startup Not Defined!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Limit',
                'helper' => 'Each virtual core is 100%. Set 0 for unrestricted CPU time.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: leave blank for all cores. Examples: 0, 0-1,3, or 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Allocated Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'allocated_swap' => [
                'label' => 'Allocated Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Space Limit',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'block_io_proportion' => [
                'label' => 'Block IO Proportion',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Disable OOM Killer',
                'helper' => 'Enabling OOM killer may cause server processes to exit unexpectedly.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the Docker image that will be used to run this server.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be transferred to.',
            ],
            'transfer_allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstall Server',
            'toggle_install_status' => 'Toggle Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'confirm' => 'Confirm',
            'delete_server' => 'Delete Server',
            'forcibly_delete_server' => 'Forcibly Delete Server',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primary',
            'notes' => 'Notes',
            'created' => 'Created',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'No Alias Assigned',
        ],

        'actions' => [
            'make_primary' => 'Make Primary',
        ],
    ],

    'databases' => [
        'title' => 'Databases',

        'table' => [
            'database' => 'Database',
            'username' => 'Username',
            'remote' => 'Remote',
            'host' => 'Host',
            'max_connections' => 'Max Connections',
            'created' => 'Created',
        ],

        'placeholder' => [
            'unlimited' => 'Unlimited',
        ],

        'actions' => [
            'create_database' => 'Create Database',
            'reset_password' => 'Reset Password',
            'delete' => 'Delete',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Database Name',
                'helper' => 'The panel will prefix this with the server ID, matching the old admin panel.',
            ],
            'database_host' => [
                'label' => 'Database Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max Connections',
            ],
        ],
    ],
];
