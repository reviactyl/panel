<?php

return [
    'title' => 'Historiska mätvärden',
    'error' => 'Kunde inte ladda historisk statistik.',
    'time_range' => [
        'last_24_hours' => 'Senaste 24 timmarna',
        'last_3_days' => 'Senaste 3 dagarna',
        'last_7_days' => 'Senaste 7 dagarna',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU-historik',
            'label' => 'CPU-användning (%)',
        ],
        'memory' => [
            'title' => 'Minneshistorik',
            'label' => 'Minnesanvändning (:unit)',
        ],
        'disk' => [
            'title' => 'Diskhistorik',
            'label' => 'Diskanvändning (:unit)',
        ],
        'network' => [
            'title' => 'Nätverkshistorik',
            'rx_label' => 'Nätverk RX (:unit)',
            'tx_label' => 'Nätverk TX (:unit)',
        ],
    ],
];
