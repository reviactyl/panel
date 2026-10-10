<?php

return [
    'title' => 'Métricas históricas',
    'error' => 'No se pueden cargar estadísticas históricas.',
    'time_range' => [
        'last_24_hours' => 'Últimas 24 horas',
        'last_3_days' => 'Últimos 3 días',
        'last_7_days' => 'Últimos 7 días',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'Historial de la CPU',
            'label' => 'Uso de CPU (%)',
        ],
        'memory' => [
            'title' => 'Historia de la memoria',
            'label' => 'Uso de memoria (:unit)',
        ],
        'disk' => [
            'title' => 'Historial de disco',
            'label' => 'Uso de disco (:unit)',
        ],
        'network' => [
            'title' => 'Historial de la red',
            'rx_label' => 'RX de red (:unit)',
            'tx_label' => 'Transmisión de red (:unit)',
        ],
    ],
];
