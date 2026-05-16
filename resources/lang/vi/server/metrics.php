<?php

return [
    'title' => 'Số liệu lịch sử',
    'error' => 'Không thể tải số liệu thống kê lịch sử.',
    'time_range' => [
        'last_24_hours' => '24 giờ qua',
        'last_3_days' => '3 ngày qua',
        'last_7_days' => '7 ngày qua',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'Lịch sử CPU',
            'label' => 'Mức sử dụng CPU (%)',
        ],
        'memory' => [
            'title' => 'Lịch sử bộ nhớ',
            'label' => 'Mức sử dụng bộ nhớ (MB)',
        ],
        'disk' => [
            'title' => 'Lịch sử đĩa',
            'label' => 'Mức sử dụng đĩa (MB)',
        ],
        'network' => [
            'title' => 'Lịch sử mạng',
            'rx_label' => 'Mạng RX (MB)',
            'tx_label' => 'Mạng TX (MB)',
        ],
    ],
];
