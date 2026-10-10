<?php

return [
    'title' => 'Mesures historiques',
    'error' => 'Impossible de charger les statistiques historiques.',
    'time_range' => [
        'last_24_hours' => 'Dernières 24 heures',
        'last_3_days' => '3 derniers jours',
        'last_7_days' => '7 derniers jours',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'Historique du processeur',
            'label' => 'Utilisation du processeur (%)',
        ],
        'memory' => [
            'title' => 'Historique de la mémoire',
            'label' => 'Utilisation de la mémoire (:unit)',
        ],
        'disk' => [
            'title' => 'Historique du disque',
            'label' => 'Utilisation du disque (:unit)',
        ],
        'network' => [
            'title' => 'Historique du réseau',
            'rx_label' => 'Réception réseau (:unit)',
            'tx_label' => 'Émission réseau (:unit)',
        ],
    ],
];
