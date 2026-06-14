<?php

return [

    'tabs' => [
        'configuration' => 'Egg 設定',
    ],

    'sections' => [
        'configuration' => [
            'title' => '設定',
        ],
        'identity' => [
            'title' => 'ID 情報',
        ],
        'docker_images' => [
            'title' => 'Docker イメージ',
            'description' => 'この Egg を使用するサーバーで利用可能な Docker イメージ。1 行に 1 つ入力すること。',
        ],
        'process_management' => [
            'title' => 'プロセス管理',
        ],
        'variables' => [
            'title' => '変数',
        ],
        'install_script' => [
            'title' => 'インストールスクリプト',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'name' => '名前',
        'author' => '作成者',
        'image' => 'イメージ',
        'description' => '説明',
        'image_name' => 'イメージ名',
        'image_uri' => 'イメージ URI',
        'add_docker_image' => 'Docker イメージを追加',
        'force_outgoing_ip' => '送信 IP を強制',
        'features' => '機能',
        'startup' => 'スタートアップコマンド',
        'config_stop' => '停止コマンド',
        'config_from' => '設定のコピー元',
        'config_startup' => '起動設定 (JSON)',
        'config_logs' => 'ログ設定 (JSON)',
        'config_files' => '設定ファイル (JSON)',
        'file_denylist' => 'ファイル拒否リスト',
        'env_variable' => '環境変数',
        'user_viewable' => 'ユーザーが閲覧可能',
        'user_editable' => 'ユーザーが編集可能',
        'rules' => '入力ルール',
        'default_value' => 'デフォルト値',
        'script_install' => 'インストールスクリプト',
        'script_container' => 'スクリプトコンテナ',
        'script_entry' => 'スクリプトエントリーポイントコマンド',
        'copy_script_from' => 'スクリプトのコピー元',
        'script_is_privileged' => '特権モード',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'すべての送信ネットワークトラフィックのソース IP を、サーバーのプライマリ割り当て IP に NAT する。',
        'features' => 'Egg に属する追加機能。パネルの追加修正を設定するのに便利である。',
        'file_denylist' => 'ユーザーが編集できないファイル。',
        'script_is_privileged' => 'インストールスクリプトを特権コンテナ (root) として実行する。',
    ],

    'actions' => [
        'export' => 'エクスポート',
        'create' => 'Egg を作成',
        'edit' => '編集',
    ],

    'notices' => [
        'cannot_delete' => 'Egg を削除できません',
        'cannot_delete_body' => 'この Egg には :count 個のサーバーが関連付けられている。先に削除または再割り当てを行うこと。',
        'cannot_delete_multiple' => 'サーバーのある Egg は削除できません',
        'cannot_delete_multiple_body' => ':count 個の Egg に関連するサーバーがあり、スキップされた。',
    ],

];
