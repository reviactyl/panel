<?php

return [
    'title' => '历史指标',
    'error' => '无法加载历史统计数据。',
    'time_range' => [
        'last_24_hours' => '过去 24 小时',
        'last_3_days' => '过去 3 天',
        'last_7_days' => '过去 7 天',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU 历史',
            'label' => 'CPU 使用率（%）',
        ],
        'memory' => [
            'title' => '内存历史',
            'label' => '内存使用量(MB)',
        ],
        'disk' => [
            'title' => '磁盘历史',
            'label' => '磁盘使用量(MB)',
        ],
        'network' => [
            'title' => '网络历史',
            'rx_label' => '网络接收量(MB)',
            'tx_label' => '网络发送量(MB)',
        ],
    ],
];