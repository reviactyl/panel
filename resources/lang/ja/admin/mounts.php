<?php

return [

    'label' => 'マウント',
    'plural_label' => 'マウント',

    'sections' => [
        'configuration' => 'マウント設定',
    ],

    'fields' => [
        'name' => '名前',
        'description' => '説明',
        'source' => 'ソースパス',
        'target' => 'ターゲットパス',
        'read_only' => '読み取り専用',
        'user_mountable' => 'ユーザーマウント可能',
    ],

    'helpers' => [
        'name' => 'このマウントを他と区別するための一意の名前。',
        'description' => 'このマウントの人間が読みやすい詳細な説明。',
        'source' => 'コンテナにマウントするホストマシン上のファイルパス。',
        'target' => 'これをマウントするコンテナ内のパス。',
        'read_only' => '設定すると、コンテナ内でマウントは読み取り専用になる。',
        'user_mountable' => '設定すると、ユーザーが自分のサーバーにマウントできるようになる。',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => '名前',
        'source' => 'ソース',
        'target' => 'ターゲット',
        'read_only' => '読み取り専用',
        'user_mountable' => 'ユーザーマウント可能',
    ],

    'actions' => [
        'attach_egg' => 'Egg をアタッチ',
        'attach_node' => 'Node をアタッチ',
    ],

];
