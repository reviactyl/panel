<?php

return [

    'label' => 'エクステンション',
    'plural-label' => 'エクステンション',

    'columns' => [
        'id' => 'ID',
        'name' => '名前',
        'version' => 'バージョン',
        'author' => '作成者',
        'enabled' => '有効',
        'updated' => '更新日',
        'manifest_json' => 'マニフェスト JSON',
    ],

    'modals' => [
        'manifest' => 'エクステンションマニフェスト',
    ],

    'actions' => [
        'edit' => '編集',
        'upload' => 'アップロード',
        'manifest' => 'マニフェストを表示',
        'disable' => '無効化',
        'enable' => '有効化',
        'delete' => '削除',
        'close' => '閉じる',
    ],

    'alerts' => [
        'enabled' => 'エクステンションを有効化しました。',
        'enable_failed' => 'エクステンションの有効化に失敗しました。',
        'disabled' => 'エクステンションを無効化しました。',
        'disable_failed' => 'エクステンションの無効化に失敗しました。',
        'uninstalled' => 'エクステンションをアンインストールしました。',
        'uninstall_failed' => 'エクステンションのアンインストールに失敗しました。',
        'could_not_locate_file' => 'アップロードされたパッケージファイルが見つかりませんでした。',
        'invalid_file_type' => '.rext ファイルのみ許可されています。',
        'upload_hint' => '.rext エクステンションパッケージのみ許可されています。',
        'install_failed' => 'エクステンションのインストールに失敗しました。',
        'install_success' => ':name (:version) を正常にインストールしました。',
    ],

];
