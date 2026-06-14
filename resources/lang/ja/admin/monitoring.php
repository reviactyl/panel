<?php

return [
    'navigation' => [
        'label' => 'モニタリング',
        'group' => '管理',
    ],

    'page' => [
        'title' => 'モニタリング',
        'heading' => 'リアルタイムモニタリング',
    ],

    'actions' => [
        'refresh' => 'データを更新',
    ],

    'selector' => [
        'label' => 'Node を選択',
        'placeholder' => 'Node を選択...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU 使用率',
        'cpu_cores' => ':count コア利用可能',
        'memory_usage' => 'メモリ使用量',
        'disk_usage' => 'ディスク使用量',
        'network_traffic' => 'ネットワークトラフィック',
        'uptime' => '稼働時間',
        'last_updated' => '最終更新',
        'no_node' => 'Node が選択されていません',
        'no_node_desc' => 'モニタリングデータを表示するには Node を選択してください',
        'no_node_hint' => '上のドロップダウンを使用してください',
        'error' => 'エラー',
        'error_desc' => 'モニタリングデータを読み込めません',
        'error_fetch' => 'Agent からデータを取得できません',
        'error_node_gone' => 'Node が存在しなくなりました',
    ],

    'details' => [
        'heading' => 'システム詳細',
        'button' => '詳細',
        'close' => '閉じる',
        'no_data' => 'データがありません。Node がオンラインであることを確認してください。',

        'cpu_section' => 'CPU',
        'cpu_total' => '合計使用率',
        'cpu_cores' => 'コア数',
        'per_core' => 'コアごとの使用率',

        'memory_section' => 'メモリ',
        'total_memory' => '合計',
        'used_memory' => '使用中',
        'free_memory' => '空き',
        'available_memory' => '利用可能',

        'swap_section' => 'スワップ',
        'swap_none' => 'この Node にスワップは設定されていません。',
        'swap_total' => '合計',
        'swap_used' => '使用中',
        'swap_free' => '空き',
        'swap_usage' => '使用率',

        'network_section' => 'ネットワーク',
        'bytes_sent' => '送信バイト数',
        'bytes_recv' => '受信バイト数',
        'packets_sent' => '送信パケット数',
        'packets_received' => '受信パケット数',

        'runtime_section' => 'ランタイム',
        'go_version' => 'Go バージョン',
        'arch' => 'アーキテクチャ',
        'goroutines' => 'ゴルーチン数',
        'uptime' => '稼働時間',
    ],
    'servers' => [
        'heading' => 'サーバー使用状況',
        'no_node' => 'サーバーの使用状況を表示するには Node を選択してください。',
        'no_servers' => 'この Node にサーバーが見つかりません。',
        'error_fetch' => 'Agent からサーバーデータを取得できません。',
        'col' => [
            'name' => 'サーバー',
            'state' => '状態',
            'cpu' => 'CPU',
            'memory' => 'メモリ',
            'disk' => 'ディスク',
            'network' => 'ネットワーク',
            'uptime' => '稼働時間',
        ],
        'states' => [
            'running' => '実行中',
            'starting' => '起動中',
            'stopping' => '停止中',
            'offline' => 'オフライン',
            'crashed' => 'クラッシュ',
            'unknown' => '不明',
        ],
    ],
];
