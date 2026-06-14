<?php

return [
    'daemon_connection_failed' => 'デーモンとの通信中に HTTP/:code レスポンスコードが返される例外が発生した。この例外はログに記録された。',
    'node' => [
        'servers_attached' => 'Node を削除するには、リンクされたサーバーが存在しない必要がある。',
        'daemon_off_config_updated' => 'デーモンの設定は更新されたが、デーモン上の設定ファイルを自動更新しようとした際にエラーが発生した。変更を適用するには、デーモンの設定ファイル (config.yml) を手動で更新する必要がある。',
    ],
    'allocations' => [
        'server_using' => 'このアロケーションには現在サーバーが割り当てられている。アロケーションを削除できるのは、サーバーが割り当てられていない場合のみである。',
        'too_many_ports' => '一度に 1000 以上のポートを単一の範囲で追加することはサポートされていない。',
        'invalid_mapping' => ':port に指定されたマッピングは無効であり、処理できなかった。',
        'cidr_out_of_range' => 'CIDR 記法では /25 から /32 の間のマスクのみ使用できる。',
        'port_out_of_range' => 'アロケーションのポートは 1024 より大きく 65535 以下である必要がある。',
    ],
    'nest' => [
        'delete_has_servers' => 'アクティブなサーバーが紐付いている Nest はパネルから削除できない。',
        'egg' => [
            'delete_has_servers' => 'アクティブなサーバーが紐付いている Egg はパネルから削除できない。',
            'invalid_copy_id' => 'スクリプトのコピー元として選択された Egg が存在しないか、そのスクリプト自体がコピーである。',
            'must_be_child' => 'この Egg の「設定のコピー元」ディレクティブは、選択された Nest の子オプションである必要がある。',
            'has_children' => 'この Egg は 1 つ以上の他の Egg の親である。この Egg を削除する前に、それらの Egg を先に削除すること。',
        ],
        'variables' => [
            'env_not_unique' => '環境変数 :name はこの Egg 内で一意である必要がある。',
            'reserved_name' => '環境変数 :name は保護されており、変数に割り当てることはできない。',
            'bad_validation_rule' => '検証ルール ":rule" はこのアプリケーションに対して有効なルールではない。',
        ],
        'importer' => [
            'json_error' => 'JSON ファイルの解析中にエラーが発生した: :error',
            'file_error' => '提供された JSON ファイルが無効である。',
            'invalid_json_provided' => '提供された JSON ファイルは認識可能な形式ではない。',
        ],
    ],
    'subusers' => [
        'editing_self' => '自分自身のサブユーザーアカウントを編集することはできない。',
        'user_is_owner' => 'サーバーのオーナーをこのサーバーのサブユーザーとして追加することはできない。',
        'subuser_exists' => 'そのメールアドレスを持つユーザーはすでにこのサーバーのサブユーザーとして割り当てられている。',
    ],
    'databases' => [
        'delete_has_databases' => 'アクティブなデータベースがリンクされているデータベースホストサーバーは削除できない。',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'チェーンされたタスクの最大インターバル時間は 15 分である。',
    ],
    'locations' => [
        'has_nodes' => 'アクティブな Node が紐付いているロケーションは削除できない。',
    ],
    'users' => [
        'node_revocation_failed' => '<a href=":link">Node #:node</a> のキー失効に失敗した。:error',
    ],
    'deployment' => [
        'no_viable_nodes' => '自動デプロイの要件を満たす Node が見つからなかった。',
        'no_viable_allocations' => '自動デプロイの要件を満たすアロケーションが見つからなかった。',
    ],
    'api' => [
        'resource_not_found' => 'リクエストされたリソースはこのサーバーに存在しない。',
    ],
    'social' => [
        'unlink_only_login' => '最初にパスワードを設定せずに、唯一のログイン方法のリンクを解除することはできない。',
    ],
];
