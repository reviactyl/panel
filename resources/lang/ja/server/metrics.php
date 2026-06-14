<?php

return [
    'title' => '履歴メトリクス',
    'error' => '履歴統計を読み込めません。',
    'time_range' => [
        'last_24_hours' => '過去 24 時間',
        'last_3_days' => '過去 3 日間',
        'last_7_days' => '過去 7 日間',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU 履歴',
            'label' => 'CPU 使用率 (%)',
        ],
        'memory' => [
            'title' => 'メモリ履歴',
            'label' => 'メモリ使用量 (MB)',
        ],
        'disk' => [
            'title' => 'ディスク履歴',
            'label' => 'ディスク使用量 (MB)',
        ],
        'network' => [
            'title' => 'ネットワーク履歴',
            'rx_label' => 'ネットワーク受信 (MB)',
            'tx_label' => 'ネットワーク送信 (MB)',
        ],
    ],
];
