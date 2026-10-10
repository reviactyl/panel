<?php

return [
    'title' => 'Historical Metrics',
    'error' => 'Unable to load historical stats.',
    'time_range' => [
        'last_24_hours' => 'Last 24 Hours',
        'last_3_days' => 'Last 3 Days',
        'last_7_days' => 'Last 7 Days',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU History',
            'label' => 'CPU Usage (%)',
        ],
        'memory' => [
            'title' => 'Memory History',
            'label' => 'Memory Usage (:unit)',
        ],
        'disk' => [
            'title' => 'Disk History',
            'label' => 'Disk Usage (:unit)',
        ],
        'network' => [
            'title' => 'Network History',
            'rx_label' => 'Network RX (:unit)',
            'tx_label' => 'Network TX (:unit)',
        ],
    ],
];
