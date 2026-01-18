<?php

return [
    'title' => 'Metrik Historis',
    'error' => 'Tidak dapat memuat statistik historis.',
    'time_range' => [
        'last_24_hours' => '24 Jam Terakhir',
        'last_3_days' => '3 Hari Terakhir',
        'last_7_days' => '7 Hari Terakhir',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'Riwayat CPU',
            'label' => 'Penggunaan CPU (%)',
        ],
        'memory' => [
            'title' => 'Riwayat Memori',
            'label' => 'Penggunaan Memori (MB)',
        ],
        'disk' => [
            'title' => 'Riwayat Disk',
            'label' => 'Penggunaan Disk (MB)',
        ],
        'network' => [
            'title' => 'Riwayat Jaringan',
            'rx_label' => 'Jaringan RX (MB)',
            'tx_label' => 'Jaringan TX (MB)',
        ],
    ],
];
