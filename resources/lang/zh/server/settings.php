<?php

return [
    'title' => '设置',
    'sftp' => [
        'title' => 'SFTP 详情',
        'address' => '服务器地址',
        'username' => '用户名',
        'password' => '您的 SFTP 密码与此面板使用的密码相同。',
        'button' => '启动 SFTP',
    ],
    'info' => [
        'title' => 'Debug 信息',
        'node' => '节点',
        'server' => '服务器 ID',
        'public-status-page' => '公共状态页面',
    ],
    'rename' => [
        'title' => '更改服务器详情',
        'name' => '服务器名称',
        'description' => '服务器描述',
        'button' => '保存',
    ],
    'reinstall' => [
        'title' => '重新安装服务器',
        'confirm-title' => '确认服务器重新安装',
        'confirm' => '是，重新安装服务器',
        'info' => '您的服务器将被停止，在此过程中某些文件可能会被删除或修改，您确定要继续吗？',
        'info-1' => '重新安装您的服务器将停止它，并运行最初设置它的安装脚本。',
        'info-2' => '在此过程中，某些文件可能会被删除或修改，请在继续前备份您的数据。',
        'button' => '重新安装服务器',
    ],
];
