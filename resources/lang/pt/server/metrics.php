<?php

return [
    'title' => 'Métricas Históricas
',
    'error' => 'Não foi possível carregar as estatísticas históricas.',
    'time_range' => [
        'last_24_hours' => 'Últimas 24 horas',
        'last_3_days' => 'Últimos 3 dias',
        'last_7_days' => 'Últimos 7 dias',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'Histórico de CPU',
            'label' => 'Uso de CPU (%)',
        ],
        'memory' => [
            'title' => 'Histórico de Memória',
            'label' => 'Uso de Memória (MB)',
        ],
        'disk' => [
            'title' => 'Histórico de Armazenamento',
            'label' => 'Uso de Armazenamento (MB)',
        ],
        'network' => [
            'title' => 'Histórico de Rede',
            'rx_label' => 'Entrada (MB)',
            'tx_label' => 'Saída (MB)',
        ],
    ],
];