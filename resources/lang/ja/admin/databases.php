<?php

return [

    'label' => 'データベース',
    'plural-label' => 'データベース',

    'none' => 'なし',

    'sections' => [
        'host_details' => [
            'title' => 'ホスト詳細',
            'description' => 'データベースホストの接続設定を構成する。',
        ],

        'authentication' => [
            'title' => '認証',
        ],

        'linked_node' => [
            'title' => 'リンクされた Node',
        ],
    ],

    'placeholders' => [
        'name' => 'Production MySQL',
    ],

    'helpers' => [
        'host' => 'データベースサーバーのホスト名または IP アドレス。',
        'linked_node' => '任意。このホストを特定の Node にリンクする。',
    ],

    'fields' => [
        'linked_node' => 'リンクされた Node',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => '名前',
        'host' => 'ホスト',
        'port' => 'ポート',
        'username' => 'ユーザー名',
        'linked_node' => 'リンクされた Node',
        'databases' => 'データベース',
        'created' => '作成日',
    ],

    'actions' => [
        'edit' => '編集',
        'delete' => '削除',
    ],

    'errors' => [
        'cannot_delete' => '関連するデータベースが存在するデータベースホストは削除できない。',
    ],

];
