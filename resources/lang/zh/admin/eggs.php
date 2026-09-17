<?php

return [

    'tabs' => [
        'configuration' => '鸡蛋配置',
    ],

    'sections' => [
        'configuration' => [
            'title' => '配置',
        ],
        'identity' => [
            'title' => '身份',
        ],
        'docker_images' => [
            'title' => 'Docker 镜像',
            'description' => '使用此egg的服务器可以使用docker镜像。每行输入一个。',
        ],
        'process_management' => [
            'title' => '流程管理',
        ],
        'variables' => [
            'title' => '变量',
        ],
        'install_script' => [
            'title' => '安装脚本',
        ],
    ],

    'fields' => [
        'nest' => '巢',
        'uuid' => '通用唯一标识符',
        'name' => '姓名',
        'author' => '作者',
        'image' => '图像',
        'description' => '描述',
        'image_name' => '图片名称',
        'image_uri' => '图片URI',
        'add_docker_image' => '添加 Docker 镜像',
        'force_outgoing_ip' => '强制传出 IP',
        'features' => '特征',
        'startup' => '启动命令',
        'config_stop' => '停止命令',
        'config_from' => '复制设置自',
        'config_startup' => '启动配置 (JSON)',
        'config_logs' => '日志配置（JSON）',
        'config_files' => '配置文件 (JSON)',
        'file_denylist' => '文件拒绝名单',
        'env_variable' => '环境变量',
        'user_viewable' => '用户可以查看',
        'user_editable' => '用户可以编辑',
        'rules' => '输入规则',
        'default_value' => '默认值',
        'script_install' => '安装脚本',
        'script_container' => '脚本容器',
        'script_entry' => '脚本入口点命令',
        'copy_script_from' => '复制脚本自',
        'script_is_privileged' => '特权',
    ],

    'helpers' => [
        'force_outgoing_ip' => '强制所有传出网络流量将其源 IP NAT 到服务器主要分配 IP 的 IP。',
        'features' => '属于鸡蛋的附加功能。对于配置其他面板修改很有用。',
        'file_denylist' => '用户不应编辑的文件。',
        'script_is_privileged' => '作为特权容器（root）运行安装脚本。',
    ],

    'actions' => [
        'export' => '出口',
        'create' => '创造鸡蛋',
        'edit' => '编辑',
    ],

    'notices' => [
        'cannot_delete' => '无法删除鸡蛋',
        'cannot_delete_body' => '该彩蛋与 :count 服务器关联。请先删除或重新分配它们。',
        'cannot_delete_multiple' => '无法使用服务器删除egg',
        'cannot_delete_multiple_body' => ':count 蛋具有关联的服务器并被跳过。',
    ],

];
