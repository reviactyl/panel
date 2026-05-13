<?php

return [
    'navigation' => [
        'label' => 'Surveillance',
        'group' => 'Administration',
    ],

    'page' => [
        'title' => 'Surveillance',
        'heading' => 'Surveillance en direct',
    ],

    'actions' => [
        'refresh' => 'Actualiser les données',
    ],

    'selector' => [
        'label' => 'Sélectionner un nœud',
        'placeholder' => 'Sélectionnez un nœud...',
    ],

    'stats' => [
        'cpu_usage' => 'Utilisation du processeur',
        'cpu_cores' => 'Noyaux :count disponibles',
        'memory_usage' => 'Utilisation de la mémoire',
        'disk_usage' => 'Utilisation du disque',
        'network_traffic' => 'Trafic réseau',
        'uptime' => 'Temps de disponibilité',
        'goroutines' => 'Les goroutines :count',
        'last_updated' => 'Dernière mise à jour',
        'no_node' => 'Aucun nœud sélectionné',
        'no_node_desc' => 'Veuillez sélectionner un nœud pour afficher les données de surveillance',
        'no_node_hint' => 'Utilisez le menu déroulant ci-dessus',
        'error' => 'Erreur',
        'error_desc' => 'Impossible de charger les données de surveillance',
        'error_fetch' => 'Impossible de récupérer les données de l\'agent',
        'error_node_gone' => 'Le nœud n\'existe plus',
    ],

    'details' => [
        'heading' => 'Détails du système',
        'button' => 'Détails',
        'close' => 'Fermer',
        'no_data' => 'Aucune donnée disponible. Assurez-vous que le nœud est en ligne.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Utilisation totale',
        'cpu_cores' => 'Noyaux',
        'per_core' => 'Utilisation par cœur',

        'memory_section' => 'Mémoire',
        'total_memory' => 'Total',
        'used_memory' => 'Utilisé',
        'free_memory' => 'Gratuit',
        'available_memory' => 'Disponible',

        'swap_section' => 'Échanger',
        'swap_none' => 'Aucun swap configuré sur ce nœud.',
        'swap_total' => 'Total',
        'swap_used' => 'Utilisé',
        'swap_free' => 'Gratuit',
        'swap_usage' => 'Usage',

        'network_section' => 'Réseau',
        'bytes_sent' => 'Octets envoyés',
        'bytes_recv' => 'Octets reçus',
        'packets_sent' => 'Paquets envoyés',
        'packets_received' => 'Paquets reçus',

        'runtime_section' => 'Durée d\'exécution',
        'go_version' => 'Aller à la version',
        'arch' => 'Architecture',
        'goroutines' => 'Goroutines',
        'uptime' => 'Temps de disponibilité',
    ],
    'servers' => [
        'heading' => 'Utilisation du serveur',
        'no_node' => 'Sélectionnez un nœud pour afficher l\'utilisation du serveur.',
        'no_servers' => 'Aucun serveur trouvé sur ce nœud.',
        'error_fetch' => 'Impossible de récupérer les données du serveur depuis l\'agent.',
        'col' => [
            'name' => 'Serveur',
            'state' => 'État',
            'cpu' => 'CPU',
            'memory' => 'Mémoire',
            'disk' => 'Disque',
            'network' => 'Réseau',
            'uptime' => 'Temps de disponibilité',
        ],
        'states' => [
            'running' => 'En cours d\'exécution',
            'starting' => 'Départ',
            'stopping' => 'Arrêt',
            'offline' => 'Hors ligne',
            'crashed' => 'Crashé',
            'unknown' => 'Inconnu',
        ],
    ],
];
