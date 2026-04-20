<?php

return [
    'title' => 'Geçmiş Metrikler',
    'error' => 'Geçmiş istatistikler yüklenemiyor.',
    'time_range' => [
        'last_24_hours' => 'Son 24 Saat',
        'last_3_days' => 'Son 3 Gün',
        'last_7_days' => 'Son 7 Gün',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU Geçmişi',
            'label' => 'CPU Kullanımı (%)',
        ],
        'memory' => [
            'title' => 'Bellek Geçmişi',
            'label' => 'Bellek Kullanımı (MB)',
        ],
        'disk' => [
            'title' => 'Disk Geçmişi',
            'label' => 'Disk Kullanımı (MB)',
        ],
        'network' => [
            'title' => 'Ağ Geçmişi',
            'rx_label' => 'Ağ RX (MB)',
            'tx_label' => 'Ağ TX (MB)',
        ],
    ],
];