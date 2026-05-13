<?php

return [

    'label' => '扩大',
    'plural-label' => '扩展',

    'columns' => [
        'id' => 'ID',
        'name' => '姓名',
        'version' => '版本',
        'author' => '作者',
        'enabled' => '启用',
        'updated' => '已更新',
        'manifest_json' => '清单 JSON',
    ],

    'modals' => [
        'manifest' => '扩展清单',
    ],

    'actions' => [
        'edit' => '编辑',
        'upload' => '上传',
        'manifest' => '查看清单',
        'disable' => '禁用',
        'enable' => '使能够',
        'delete' => '删除',
        'close' => '关闭',
    ],

    'alerts' => [
        'enabled' => '扩展已启用。',
        'enable_failed' => '无法启用扩展。',
        'disabled' => '扩展已禁用。',
        'disable_failed' => '无法禁用扩展。',
        'uninstalled' => '扩展已卸载。',
        'uninstall_failed' => '卸载扩展程序失败。',
        'could_not_locate_file' => '无法找到上传的包文件。',
        'invalid_file_type' => '仅允许 .rext 文件。',
        'upload_hint' => '仅允许 .rext 扩展包。',
        'install_failed' => '扩展安装失败。',
        'install_success' => ':name（:version）安装成功。',
    ],

];
