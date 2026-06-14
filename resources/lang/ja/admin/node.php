<?php

return [
    'label' => 'Node',
    'plural-label' => 'Node',

    'sections' => [
        'overview' => [
            'title' => '概要',
            'information-label' => 'Node 情報',
            'version-label' => 'Agent バージョン',
            'architecture-label' => 'アーキテクチャ',
            'kernel-label' => 'カーネル',
            'cpus-label' => 'CPU スレッド数',
            'cpu-usage-label' => 'CPU 使用率',
            'memory-usage-label' => 'メモリ使用量',
            'disk-usage-label' => 'ディスク使用量',
        ],
        'tabs' => [
            'title' => 'Node 設定',
        ],
        'identity' => [
            'title' => 'ID 情報',
            'description' => '基本的な Node 情報。',
        ],
        'connection' => [
            'title' => '接続詳細',
            'description' => 'この Node への接続方法を設定する。',
        ],
        'resources' => [
            'title' => 'リソース割り当て',
            'description' => 'この Node のメモリとディスクの上限を定義する。',
        ],
        'daemon' => [
            'title' => 'デーモン設定',
            'description' => 'デーモン固有の設定を構成する。',
        ],
        'configuration' => [
            'title' => '設定',
            'config_description' => '設定ファイル',
            'deploy_description' => '対象サーバーで Agent を設定するためのカスタムデプロイコマンドを生成する。',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => '公開',
            'helper' => 'Node をプライベートに設定すると、この Node への自動デプロイが無効になる。',
        ],
        'name' => [
            'label' => '名前',
            'placeholder' => 'Node 名',
            'helper' => 'この Node のわかりやすい名前。',
        ],
        'description' => [
            'label' => '説明',
            'placeholder' => 'Node の説明',
            'helper' => 'この Node の任意の説明。',
        ],
        'location' => [
            'label' => 'ロケーション',
            'helper' => 'この Node が割り当てられているロケーション。',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => '完全修飾ドメイン名または IP アドレス。',
        ],
        'ssl' => [
            'label' => 'SSL を使用',
            'helper' => 'この Node のデーモンが安全な通信のために SSL を使用するよう設定されているかどうか。',
            'helper_forced' => 'このパネルは HTTPS で動作しているため、この Node では SSL が強制される。',
        ],
        'behind_proxy' => [
            'label' => 'プロキシ経由',
            'helper' => 'この Node が Cloudflare などのプロキシの背後にある場合は有効にする。',
        ],
        'maintenance_mode' => [
            'label' => 'メンテナンスモード',
            'helper' => 'この Node への新しいサーバー作成を防ぐ。',
        ],
        'memory' => [
            'label' => '合計メモリ',
            'helper' => 'この Node で利用可能なメモリ（MiB 単位）。',
        ],
        'memory_overallocate' => [
            'label' => 'メモリ過割り当て',
            'helper' => '過割り当てするメモリのパーセンテージ。チェックを無効にするには -1 を使用。',
        ],
        'disk' => [
            'label' => '合計ディスク容量',
            'helper' => 'この Node で利用可能なディスク容量（MiB 単位）。',
        ],
        'disk_overallocate' => [
            'label' => 'ディスク過割り当て',
            'helper' => '過割り当てするディスクのパーセンテージ。チェックを無効にするには -1 を使用。',
        ],
        'upload_size' => [
            'label' => '最大アップロードサイズ',
            'helper' => 'Web パネル経由で許可される最大ファイルアップロードサイズ。',
        ],
        'daemon_base' => [
            'label' => 'ベースディレクトリ',
            'helper' => 'サーバーファイルが保存されるディレクトリ。',
        ],
        'daemon_listen' => [
            'label' => 'デーモンポート',
            'helper' => 'デーモンが HTTP 通信でリッスンするポート。',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP ポート',
            'helper' => 'SFTP 接続に使用するポート。',
        ],
        'daemon_token_id' => [
            'label' => 'トークン ID',
        ],
        'container_text' => [
            'label' => 'コンテナプレフィックス',
            'helper' => 'コンテナ名に表示されるテキストプレフィックス。',
        ],
    ],

    'table' => [
        'health' => 'ヘルス',
        'health_http_status' => 'HTTP :status',
        'health_check_console' => 'ブラウザのコンソールを確認',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => '名前',
        'location' => 'ロケーション',
        'fqdn' => 'FQDN',
        'scheme' => 'プロトコル',
        'public' => '公開',
        'behind_proxy' => 'プロキシ経由',
        'maintenance_mode' => 'メンテナンス',
        'memory' => 'メモリ',
        'memory_overallocate' => 'メモリ超過',
        'disk' => 'ディスク',
        'disk_overallocate' => 'ディスク超過',
        'upload_size' => 'アップロードサイズ',
        'daemon_listen' => 'デーモンポート',
        'daemon_sftp' => 'SFTP ポート',
        'daemon_base' => 'ベースディレクトリ',
        'servers' => 'サーバー',
        'created' => '作成日',
        'updated' => '更新日',
    ],

    'filters' => [
        'public' => '公開',
        'maintenance' => 'メンテナンス',
        'public_true' => '公開',
        'public_false' => 'プライベート',
        'maintenance_true' => 'メンテナンス中',
        'maintenance_false' => 'アクティブ',
    ],

    'actions' => [
        'create' => '作成',
        'edit' => '編集',
        'delete' => '削除',
        'view' => '表示',
        'random' => 'ランダム',
        'view_monitoring' => 'モニタリングを表示',
    ],

    'deployment' => [
        'generate_label' => 'デプロイトークンを生成',
        'modal_heading' => '自動デプロイコマンド',
        'modal_description' => 'このコマンドを Node 上で実行すると Agent が自動設定される。',
        'modal_close' => '閉じる',
        'command_label' => 'デプロイコマンド',
        'command_helper' => 'このコマンドをコピーして Node サーバーで実行すること。',
        'token_success' => 'トークンの生成に成功',
        'token_success_body' => '下のコマンドをコピーして Node で実行してください。',
        'save_first' => '最初に Node を保存してください。',
        'auto_generated_key' => '自動生成された Node デプロイキー。',
        'error' => 'トークンの生成中にエラーが発生しました。再試行してください。',
    ],

    'general' => [
        'na' => 'N/A',
        'unavailable' => '利用不可',
    ],

    'messages' => [
        'created' => 'Node が正常に作成されました。',
        'updated' => 'Node が正常に更新されました。',
        'deleted' => 'Node が正常に削除されました。',
        'cannot_delete_with_servers' => 'アクティブなサーバーがある Node は削除できない。',
    ],

    'allocations' => [
        'label' => 'アロケーション',
        'table' => [
            'ip' => 'IP',
            'port' => 'ポート',
            'alias' => 'エイリアス',
            'server' => 'サーバー',
            'notes' => 'メモ',
            'created' => '作成日',
            'unassigned' => '未割り当て',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP アドレス',
                'helper' => '単一の IP または CIDR をサポート（例: 192.0.2.1 または 192.0.2.0/24）。',
            ],
            'allocation_ports' => [
                'label' => 'ポート',
                'helper' => 'ポートまたは範囲を入力（例: 25565, 25566, 25570-25580）。',
            ],
            'allocation_alias' => [
                'label' => 'IP エイリアス',
                'helper' => 'IP の代わりに表示する任意のエイリアス。',
            ],
        ],
        'actions' => [
            'add' => 'アロケーションを追加',
            'delete' => '削除',
        ],
        'messages' => [
            'created' => 'アロケーションを追加しました。',
            'deleted' => 'アロケーションを削除しました。',
            'failed' => 'アロケーション操作に失敗しました。',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => '提供された FQDN または IP アドレスが有効な IP アドレスに解決されない。',
        'fqdn_required_for_ssl' => 'この Node で SSL を使用するには、パブリック IP アドレスに解決される完全修飾ドメイン名が必要である。',
    ],
    'notices' => [
        'allocations_added' => 'アロケーションがこの Node に正常に追加されました。',
        'node_deleted' => 'Node がパネルから正常に削除されました。',
        'location_required' => 'パネルに Node を追加する前に、少なくとも 1 つのロケーションを設定する必要がある。',
        'node_created' => '新しい Node が正常に作成された。「設定」タブにアクセスすることで、このマシンのデーモンを自動設定できる。サーバーを追加する前に、少なくとも 1 つの IP アドレスとポートを割り当てる必要がある。',
        'node_updated' => 'Node 情報が更新された。デーモン設定が変更された場合は、変更を有効にするために再起動する必要がある。',
        'unallocated_deleted' => '<code>:ip</code> の未割り当てポートをすべて削除した。',
    ],
];
