<?php

return [
    'title' => '用户',
    'exceptions' => [
        'delete_self' => '您无法删除自己的账户。',
        'user_has_servers' => '无法删除拥有活动服务器的用户。请先删除其服务器后再继续。',
    ],
    'notices' => [
        'account_created' => '账户已成功创建。',
        'account_updated' => '账户已成功更新。',
    ],
    'details' => [
        'account_details' => '账户详情',
        'external_id' => '外部 ID',
        'username' => '用户名',
        'email' => '电子邮箱地址',
        'first_name' => '名',
        'last_name' => '姓',
        'language' => '语言',
        'geolocate' => '地理定位（自动）',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'root_admin' => '超级管理员',
        'root_admin_desc' => '该用户将拥有系统中所有服务器和设置的完全访问权限。',
        'privileges' => '权限',
        'admin_status' => '管理员状态',
    ],
];
