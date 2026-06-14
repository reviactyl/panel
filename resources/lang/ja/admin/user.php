<?php

return [
    'title' => 'ユーザー',
    'exceptions' => [
        'delete_self' => '自分自身のアカウントを削除することはできない。',
        'user_has_servers' => 'アクティブなサーバーが紐付いているユーザーは削除できない。続行する前にそのユーザーのサーバーを削除すること。',
    ],
    'notices' => [
        'account_created' => 'アカウントが正常に作成されました。',
        'account_updated' => 'アカウントが正常に更新されました。',
    ],
    'details' => [
        'account_details' => 'アカウント詳細',
        'external_id' => '外部 ID',
        'username' => 'ユーザー名',
        'email' => 'メールアドレス',
        'first_name' => '名',
        'last_name' => '姓',
        'language' => '言語',
        'geolocate' => 'ジオロケート（自動）',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワードを確認',
        'root_admin' => 'ルート管理者',
        'root_admin_desc' => 'このユーザーはシステム上のすべてのサーバーと設定に完全なアクセス権を持つ。',
        'privileges' => '権限',
        'admin_status' => '管理者ステータス',
    ],
];
