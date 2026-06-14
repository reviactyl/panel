<?php

return [

    'label' => 'ロケーション',
    'plural-label' => 'ロケーション',

    'section' => [
        'title' => 'ロケーション詳細',
        'description' => 'Node を割り当て可能なロケーションを定義する。',
    ],

    'fields' => [
        'short' => [
            'label' => 'ショートコード',
            'helper' => 'このロケーションの短い識別子。',
        ],

        'long' => [
            'label' => '説明',
            'helper' => 'このロケーションの詳細な説明。',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'ショートコード',
        'long' => '説明',
        'nodes' => 'Node',
        'servers' => 'サーバー',
        'created' => '作成日',
    ],

    'actions' => [
        'edit' => '編集',
        'delete' => '削除',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => '関連する Node が存在するロケーションは削除できない。',
    ],

];
