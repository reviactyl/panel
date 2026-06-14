<?php

return [
    'label' => 'サーバー',
    'plural-label' => 'サーバー',

    'sections' => [
        'identity' => [
            'title' => 'ID 情報',
            'description' => '基本的なサーバー情報とオーナーシップ。',
        ],
        'allocation' => [
            'title' => 'アロケーション',
            'description' => 'このサーバーの Node とネットワークアロケーションを選択する。',
        ],
        'startup' => [
            'title' => 'スタートアップ',
            'description' => 'Egg、スタートアップコマンド、Docker イメージを設定する。',
        ],
        'resources' => [
            'title' => 'リソース制限',
            'description' => 'サーバーのリソース制限を定義する。',
        ],
        'feature_limits' => [
            'title' => '機能制限',
            'description' => 'データベース、アロケーション、バックアップを制限する。',
        ],
        'environment' => [
            'title' => '環境変数',
            'description' => '選択した Egg の環境値を設定する。',
        ],
    ],

    'status' => [
        'online' => 'オンライン',
        'offline' => 'オフライン',
        'starting' => '起動中',
        'stopping' => '停止中',
        'crashed' => 'クラッシュ',
        'installing' => 'インストール中',
        'restoring_backup' => 'バックアップ復元中',
        'install_failed' => 'インストール失敗',
        'reinstall_failed' => '再インストール失敗',
        'suspended' => '停止済み',
    ],

    'create' => [
        'sections' => [
            'core_details' => '基本情報',
            'allocation' => 'アロケーション管理',
            'feature_limits' => 'アプリケーション機能制限',
            'resources' => 'リソース管理',
            'nest' => 'Nest 設定',
            'docker' => 'Docker 設定',
            'startup' => 'スタートアップ設定',
            'variables' => 'サービス変数',
        ],

        'fields' => [
            'name' => [
                'label' => 'サーバー名',
                'placeholder' => 'サーバー名',
                'helper' => '使用できる文字: a-z A-Z 0-9 _ - . およびスペース。',
            ],
            'owner' => [
                'label' => 'サーバーオーナー',
                'helper' => 'サーバーオーナーのメールアドレス。',
            ],
            'description' => [
                'label' => 'サーバーの説明',
                'helper' => 'このサーバーの簡単な説明。',
            ],
            'start_on_completion' => [
                'label' => 'インストール後にサーバーを起動',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'このサーバーがデプロイされる Node。',
            ],
            'allocation' => [
                'label' => 'デフォルトアロケーション',
                'helper' => 'このサーバーに割り当てられるメインアロケーション。',
            ],
            'additional_allocations' => [
                'label' => '追加アロケーション',
                'helper' => '作成時にこのサーバーに割り当てる追加アロケーション。',
            ],
            'database_limit' => [
                'label' => 'データベース制限',
                'helper' => 'ユーザーがこのサーバーに作成できるデータベースの総数。',
            ],
            'allocation_limit' => [
                'label' => 'アロケーション制限',
                'helper' => 'ユーザーがこのサーバーに作成できるアロケーションの総数。',
            ],
            'backup_limit' => [
                'label' => 'バックアップ制限',
                'helper' => 'このサーバーに作成できるバックアップの総数。',
            ],
            'cpu' => [
                'label' => 'CPU 制限',
                'helper' => 'CPU 制限なしは 0 に設定。仮想コア 1 つが 100%。',
            ],
            'threads' => [
                'label' => 'CPU ピニング',
                'helper' => '上級: 単一の数字またはカンマ区切りリスト（例: 0、0-1,3、または 0,1,3,4）。',
            ],
            'memory' => [
                'label' => 'メモリ',
                'helper' => 'このコンテナに許可される最大メモリ量。無制限は 0 に設定。',
            ],
            'swap' => [
                'label' => 'スワップ',
                'helper' => 'スワップを無効にするには 0、無制限スワップを許可するには -1 を設定。',
            ],
            'disk' => [
                'label' => 'ディスク容量',
                'helper' => 'ディスク使用量を無制限にするには 0 を設定。',
            ],
            'io' => [
                'label' => 'ブロック IO ウェイト',
                'helper' => '上級: 他の実行中コンテナに対する相対 IO パフォーマンス。値は 10 から 1000。',
            ],
            'oom_disabled' => [
                'label' => 'OOM キラーを有効化',
                'helper' => 'メモリ制限を超えた場合にサーバーを終了する。',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'このサーバーをグループ化する Nest を選択する。',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'このサーバーの動作を定義する Egg を選択する。',
            ],
            'skip_scripts' => [
                'label' => 'Egg インストールスクリプトをスキップ',
                'helper' => '選択した Egg にインストールスクリプトが付属している場合、これをチェックしない限りインストール中にスクリプトが実行される。',
            ],
            'image' => [
                'label' => 'Docker イメージ',
                'helper' => 'ドロップダウンからイメージを選択するか、以下にカスタムイメージを入力する。',
            ],
            'custom_image' => [
                'label' => 'カスタム Docker イメージ',
                'placeholder' => 'またはカスタムイメージを入力...',
                'helper' => 'このサーバーの実行に使用されるデフォルトの Docker イメージ。',
            ],
            'startup' => [
                'label' => 'スタートアップコマンド',
                'helper' => '利用可能な代替変数: {{SERVER_MEMORY}}、{{SERVER_IP}}、{{SERVER_PORT}}。',
            ],
            'environment_placeholder' => [
                'label' => 'サービス変数を設定するには Egg を選択してください',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => '詳細モード',
            'helper' => '追加のサーバー設定オプションを表示するには切り替える。追加設定の意味を理解している場合のみ有効にすること。',
        ],
        'external_id' => [
            'label' => '外部 ID',
            'helper' => 'このサーバーの任意の一意識別子。',
        ],
        'owner' => [
            'label' => 'オーナー',
            'helper' => 'このサーバーを所有するユーザーを選択する。',
        ],
        'name' => [
            'label' => '名前',
            'placeholder' => 'サーバー名',
            'helper' => 'このサーバーの短い名前。',
        ],
        'description' => [
            'label' => '説明',
            'placeholder' => 'サーバーの説明',
            'helper' => 'このサーバーの任意の説明。',
        ],
        'node' => [
            'label' => 'Node',
            'helper' => 'このサーバーがデプロイされる Node。',
        ],
        'allocation' => [
            'label' => 'プライマリアロケーション',
            'helper' => 'このサーバーのデフォルト IP/ポートアロケーション。',
        ],
        'additional_allocations' => [
            'label' => '追加アロケーション',
            'helper' => '割り当てる任意の追加アロケーション。',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'このサーバーのサービス Nest。',
        ],
        'egg' => [
            'label' => 'Egg',
            'helper' => 'サーバーの動作を定義する Egg。',
        ],
        'startup' => [
            'label' => 'スタートアップコマンド',
            'helper' => 'サーバーのスタートアップコマンド。',
        ],
        'image' => [
            'label' => 'Docker イメージ',
            'helper' => 'このサーバーの実行に使用される Docker イメージ。',
            'custom' => 'カスタム',
        ],
        'skip_scripts' => [
            'label' => 'Egg スクリプトをスキップ',
            'helper' => '作成時に Egg インストールスクリプトをスキップする。',
        ],
        'start_on_completion' => [
            'label' => '完了後に起動',
            'helper' => 'インストール後にサーバーを自動起動する。',
        ],
        'memory' => [
            'label' => 'メモリ',
            'helper' => '合計メモリ割り当て。無制限は 0 に設定。（Minecraft Egg ではスタートアップコマンドの制約により無制限メモリは機能しない）',
        ],
        'swap' => [
            'label' => 'スワップ',
            'helper' => 'スワップメモリ割り当て。スワップを無効にするには 0、無制限は -1 を設定。',
        ],
        'disk' => [
            'label' => 'ディスク',
            'helper' => 'ディスク容量割り当て。無制限は 0 に設定。',
        ],
        'io' => [
            'label' => 'IO ウェイト',
            'helper' => '相対ディスク I/O 優先度 (10-1000)。',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'CPU 制限（パーセント）。100% は 1 コアフル、200% は 2 コアフルを意味する。',
        ],
        'enter_size_in_gib' => [
            'label' => 'GiB で入力',
            'helper' => '「GiB」サフィックスを使用して GiB 単位でサイズを入力できる（例: 10GiB = 10240MiB）。',
        ],
        'threads' => [
            'label' => 'CPU スレッド',
            'helper' => '任意のスレッドピニング。例: 0-1,3。',
        ],
        'oom_disabled' => [
            'label' => 'OOM キラーを無効化',
            'helper' => 'メモリ不足時にカーネルがプロセスを強制終了するのを防ぐ。',
        ],
        'database_limit' => [
            'label' => 'データベース制限',
            'helper' => 'データベースの最大数。',
        ],
        'allocation_limit' => [
            'label' => 'アロケーション制限',
            'helper' => 'アロケーションの最大数。',
        ],
        'backup_limit' => [
            'label' => 'バックアップ制限',
            'helper' => 'バックアップの最大数。',
        ],
        'environment' => [
            'key' => '変数',
            'value' => '値',
            'helper' => 'この Egg の環境変数。',
        ],
        'use_custom_image' => [
            'label' => 'カスタムイメージを使用',
            'helper' => 'Egg が提供するイメージの代わりにカスタム Docker イメージを使用する場合に切り替える。',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => '名前',
        'owner' => 'オーナー',
        'node' => 'Node',
        'allocation' => 'アロケーション',
        'status' => 'ステータス',
        'egg' => 'Egg',
        'memory' => 'メモリ',
        'disk' => 'ディスク',
        'cpu' => 'CPU',
        'created' => '作成日',
        'updated' => '更新日',
        'installed' => 'インストール済み',
        'no_status' => 'ステータスなし',
        'unlimited' => '無制限',
    ],

    'messages' => [
        'created' => 'サーバーが正常に作成されました。',
        'updated' => 'サーバーが正常に更新されました。',
        'deleted' => 'サーバーが正常に削除されました。',
    ],

    'actions' => [
        'edit' => '編集',
        'random' => 'ランダム',
        'toggle_install_status' => 'インストール状態を切り替え',
        'suspend' => '停止',
        'unsuspend' => '再開',
        'suspended' => '停止済み',
        'unsuspended' => '再開済み',
        'reinstall' => '再インストール',
        'delete' => '削除',
        'delete_forcibly' => '強制削除',
        'view' => '表示',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'このサーバーのデフォルトアロケーションを削除しようとしているが、代替アロケーションが存在しない。',
        'marked_as_failed' => 'このサーバーは以前のインストールが失敗したとしてマークされている。この状態ではステータスを切り替えることができない。',
        'bad_variable' => '変数 :name で検証エラーが発生した。',
        'daemon_exception' => 'デーモンとの通信中に HTTP/:code レスポンスコードが返される例外が発生した。この例外はログに記録された。（リクエスト ID: :request_id）',
        'default_allocation_not_found' => 'リクエストされたデフォルトアロケーションがこのサーバーのアロケーションに見つからなかった。',
    ],

    'alerts' => [
        'install_toggled' => 'サーバーのインストール状態を切り替えました。',
        'server_suspended' => 'サーバーが :action されました。',
        'server_reinstalled' => 'サーバーの再インストールが開始されました。',
        'server_deleted' => 'サーバーが削除されました。',
        'server_delete_failed' => 'サーバーの削除に失敗しました。',
        'startup_changed' => 'このサーバーのスタートアップ設定が更新された。このサーバーの Nest または Egg が変更された場合、今すぐ再インストールが実行される。',
        'server_created' => 'サーバーがパネルに正常に作成された。デーモンがこのサーバーのインストールを完了するまで数分かかる場合がある。',
        'build_updated' => 'このサーバーのビルド詳細が更新された。一部の変更を有効にするには再起動が必要な場合がある。',
        'suspension_toggled' => 'サーバーの停止状態が :status に変更されました。',
        'rebuild_on_boot' => 'このサーバーは Docker コンテナの再ビルドが必要とマークされた。次回のサーバー起動時に実行される。',
        'details_updated' => 'サーバーの詳細が正常に更新されました。',
        'docker_image_updated' => 'このサーバーのデフォルト Docker イメージを正常に変更した。変更を適用するには再起動が必要である。',
        'node_required' => 'パネルにサーバーを追加する前に、少なくとも 1 つの Node を設定する必要がある。',
        'transfer_nodes_required' => 'サーバーを転送するには、少なくとも 2 つの Node を設定する必要がある。',
        'transfer_started' => 'サーバーの転送が開始されました。',
        'transfer_not_viable' => '選択した Node にはこのサーバーを収容するのに必要なディスク容量またはメモリがない。',
        'primary_allocation_updated' => 'プライマリアロケーションを更新しました。',
        'database_created' => 'データベースを作成しました。',
        'database_password_reset' => 'データベースパスワードをリセットしました。',
        'database_deleted' => 'データベースを削除しました。',
    ],

    'edit' => [
        'tabs' => [
            'information' => '情報',
            'build_configuration' => 'ビルド設定',
            'startup' => 'スタートアップ',
            'manage' => '管理',
        ],

        'sections' => [
            'resource_management' => 'リソース管理',
            'application_feature_limits' => 'アプリケーション機能制限',
            'allocation_management' => 'アロケーション管理',
            'startup_command_modification' => 'スタートアップコマンド変更',
            'service_configuration' => 'サービス設定',
            'docker_image_configuration' => 'Docker イメージ設定',
            'service_variables' => 'サービス変数',
            'reinstall_server' => 'サーバー再インストール',
            'install_status' => 'インストール状態',
            'suspend_server' => 'サーバーを停止',
            'unsuspend_server' => 'サーバーを再開',
            'transfer_server' => 'サーバーを転送',
            'delete_server' => 'サーバーを削除',
        ],

        'section_descriptions' => [
            'service_configuration' => 'これらの値を変更すると再インストールが発生する場合がある。その操作のためにサーバーはただちに停止される。',
            'reinstall_server' => '割り当てられたサービススクリプトを使用してサーバーを再インストールする。サーバーデータが上書きされる場合がある。',
            'install_status' => 'インストール状態を未インストールとインストール済みの間で切り替える。',
            'suspend_server' => '実行中のプロセスを停止し、ユーザーがパネルまたは API を通じてサーバーを管理できないようにする。',
            'unsuspend_server' => 'サーバーの停止を解除し、通常のユーザーアクセスを回復する。',
            'transfer_server_transferring' => 'このサーバーは現在別の Node に転送中である。',
            'transfer_server' => 'このサーバーをこのパネルに接続された別の Node に転送する。',
            'delete_server' => 'パネルと Agent からサーバーを完全に削除する。強制削除は必要に応じて Agent での削除をスキップする。',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'サーバー名',
                'helper' => '文字制限: a-zA-Z0-9_-、スペース、および標準の印刷可能文字。',
            ],
            'server_owner' => [
                'label' => 'サーバーオーナー',
                'helper' => 'オーナーシップを変更すると、前のオーナーのデーモントークンが自動的に失効する。',
            ],
            'server_description' => [
                'label' => 'サーバーの説明',
                'helper' => 'このサーバーの簡単な説明。',
            ],
            'server_uuid' => [
                'label' => 'サーバー UUID',
            ],
            'server_uuid_short' => [
                'label' => 'サーバー UUID（短縮）',
            ],
            'external_identifier' => [
                'label' => '外部識別子',
                'helper' => '外部識別子を割り当てない場合は空欄にする。外部 ID はこのサーバーで一意である必要がある。',
            ],
            'game_port' => [
                'label' => 'ゲームポート',
                'helper' => 'このゲームサーバーのデフォルト接続アドレス。',
            ],
            'additional_ports' => [
                'label' => '追加ポート',
                'helper' => '追加ポートを割り当てまたは削除する。異なる IP 上の同一ポートを同じサーバーに割り当てることはできない。',
            ],
            'startup_command' => [
                'label' => 'スタートアップコマンド',
                'helper' => 'デフォルトで利用可能: {{SERVER_MEMORY}}、{{SERVER_IP}}、{{SERVER_PORT}}。',
            ],
            'default_startup_command' => [
                'label' => 'デフォルトスタートアップコマンド',
                'error' => 'エラー: スタートアップが定義されていません！',
            ],
            'cpu_limit' => [
                'label' => 'CPU 制限',
                'helper' => '各仮想コアは 100%。CPU 時間を無制限にするには 0 を設定。',
            ],
            'cpu_pinning' => [
                'label' => 'CPU ピニング',
                'helper' => '上級: すべてのコアを使用する場合は空欄。例: 0、0-1,3、または 0,1,3,4。',
            ],
            'allocated_memory' => [
                'label' => '割り当てメモリ',
                'helper' => 'このコンテナに許可される最大メモリ量。無制限は 0 に設定。',
            ],
            'allocated_swap' => [
                'label' => '割り当てスワップ',
                'helper' => 'スワップを無効にするには 0、無制限スワップを許可するには -1 を設定。',
            ],
            'disk_space_limit' => [
                'label' => 'ディスク容量制限',
                'helper' => 'ディスク使用量を無制限にするには 0 を設定。',
            ],
            'block_io_proportion' => [
                'label' => 'ブロック IO 比率',
                'helper' => '上級: 他の実行中コンテナに対する相対 IO パフォーマンス。値は 10 から 1000。',
            ],
            'disable_oom_killer' => [
                'label' => 'OOM キラーを無効化',
                'helper' => 'OOM キラーを有効にすると、サーバープロセスが予期せず終了する可能性がある。',
            ],
            'database_limit' => [
                'label' => 'データベース制限',
                'helper' => 'ユーザーがこのサーバーに作成できるデータベースの総数。',
            ],
            'allocation_limit' => [
                'label' => 'アロケーション制限',
                'helper' => 'ユーザーがこのサーバーに作成できるアロケーションの総数。',
            ],
            'backup_limit' => [
                'label' => 'バックアップ制限',
                'helper' => 'このサーバーに作成できるバックアップの総数。',
            ],
            'image' => [
                'label' => 'イメージ',
                'helper' => 'ドロップダウンからイメージを選択するか、以下にカスタムイメージを入力する。',
            ],
            'custom_image' => [
                'label' => 'カスタムイメージ',
                'placeholder' => 'またはカスタムイメージを入力...',
                'helper' => 'このサーバーの実行に使用される Docker イメージ。',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'このサーバーが転送される Node。',
            ],
            'transfer_allocation' => [
                'label' => 'デフォルトアロケーション',
                'helper' => 'このサーバーに割り当てられるメインアロケーション。',
            ],
            'transfer_additional_allocations' => [
                'label' => '追加アロケーション',
                'helper' => '転送時にこのサーバーに割り当てる追加アロケーション。',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'サーバーを再インストール',
            'toggle_install_status' => 'インストール状態を切り替え',
            'suspend_server' => 'サーバーを停止',
            'unsuspend_server' => 'サーバーを再開',
            'transfer_server' => 'サーバーを転送',
            'confirm' => '確認',
            'delete_server' => 'サーバーを削除',
            'forcibly_delete_server' => 'サーバーを強制削除',
        ],
    ],

    'allocations' => [
        'title' => 'アロケーション',

        'table' => [
            'ip' => 'IP',
            'port' => 'ポート',
            'alias' => 'エイリアス',
            'primary' => 'プライマリ',
            'notes' => 'メモ',
            'created' => '作成日',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'エイリアス未設定',
        ],

        'actions' => [
            'make_primary' => 'プライマリに設定',
        ],
    ],

    'databases' => [
        'title' => 'データベース',

        'table' => [
            'database' => 'データベース',
            'username' => 'ユーザー名',
            'remote' => 'リモート',
            'host' => 'ホスト',
            'max_connections' => '最大接続数',
            'created' => '作成日',
        ],

        'placeholder' => [
            'unlimited' => '無制限',
        ],

        'actions' => [
            'create_database' => 'データベースを作成',
            'reset_password' => 'パスワードをリセット',
            'delete' => '削除',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'データベース名',
                'helper' => 'パネルはこれにサーバー ID をプレフィックスとして付加する（旧管理パネルと一致）。',
            ],
            'database_host' => [
                'label' => 'データベースホスト',
            ],
            'remote' => [
                'label' => 'リモート',
            ],
            'max_connections' => [
                'label' => '最大接続数',
            ],
        ],
    ],
];
