<?php

return [

    'label' => 'Nest',
    'plural_label' => 'Nest',

    'sections' => [
        'configuration' => 'Nest 設定',
    ],

    'fields' => [
        'name' => '名前',
        'author' => '作成者',
        'description' => '説明',
    ],

    'helpers' => [
        'name' => 'この Nest を識別するための一意の名前。',
        'author' => 'この Nest の作成者。有効なメールアドレスである必要がある。',
        'description' => 'この Nest の説明。',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => '名前',
        'author' => '作成者',
        'eggs' => 'Egg',
        'servers' => 'サーバー',
    ],

    'actions' => [
        'import' => 'Egg をインポート',
    ],

    'import' => [
        'file_label' => 'Egg ファイル (JSON)',
        'nest_label' => '関連する Nest',
        'file_not_found' => 'ファイルが見つかりません',
        'file_not_found_body' => 'アップロードされたファイルが見つかりませんでした。',
        'invalid_format' => '無効なファイル形式',
        'invalid_format_body' => '予期しないファイル形式を受信しました。',
        'success' => 'Egg を正常にインポートしました',
        'failed' => 'Egg のインポートに失敗しました',
    ],

    'notices' => [
        'created' => '新しい Nest「:name」が正常に作成された。',
        'deleted' => 'リクエストされた Nest をパネルから正常に削除した。',
        'updated' => 'Nest の設定オプションを正常に更新した。',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'この Egg とその関連変数を正常にインポートした。',
            'updated_via_import' => 'この Egg は提供されたファイルを使用して更新された。',
            'deleted' => 'リクエストされた Egg をパネルから正常に削除した。',
            'updated' => 'Egg の設定を正常に更新した。',
            'script_updated' => 'Egg インストールスクリプトが更新され、サーバーのインストール時に実行される。',
            'egg_created' => '新しい Egg が正常に作成された。この新しい Egg を適用するには、実行中のデーモンを再起動する必要がある。',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => '変数「:variable」が削除された。再ビルド後はサーバーで利用できなくなる。',
            'variable_updated' => '変数「:variable」が更新された。変更を適用するには、この変数を使用しているサーバーを再ビルドする必要がある。',
            'variable_created' => '新しい変数が正常に作成され、この Egg に割り当てられた。',
        ],
    ],
];
