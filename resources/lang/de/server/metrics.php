<?php

return [
    'title' => 'Historische Metriken',
    'error' => 'Konnte Metriken nicht laden.',
    'time_range' => [
        'last_24_hours' => 'Letzte 24 Stunden',
        'last_3_days' => 'Letzte 3 Tage',
        'last_7_days' => 'Letzte 7 Tage',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU Verlauf',
            'label' => 'CPU Auslastung (%)',
        ],
        'memory' => [
            'title' => 'Arbeitsspeicher Verlauf',
            'label' => 'Arbeitsspeicher Auslastung (MB)',
        ],
        'disk' => [
            'title' => 'Speicherplatz Verlauf',
            'label' => 'Speicherplatz Nutzung (MB)',
        ],
        'network' => [
            'title' => 'Netzwerk Verlauf',
            'rx_label' => 'Empfangen (MB)',
            'tx_label' => 'Gesendet (MB)',
        ],
    ],
];