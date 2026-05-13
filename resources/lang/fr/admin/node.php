<?php

return [
    'label' => 'Nœud',
    'plural-label' => 'Nœuds',

    'sections' => [
        'overview' => [
            'title' => 'Aperçu',
            'information-label' => 'Informations sur le nœud',
            'version-label' => 'Version de l\'agent',
            'architecture-label' => 'Architecture',
            'kernel-label' => 'Noyau',
            'cpus-label' => 'Fils de processeur',
            'cpu-usage-label' => 'Utilisation du processeur',
            'memory-usage-label' => 'Utilisation de la mémoire',
            'disk-usage-label' => 'Utilisation du disque',
        ],
        'tabs' => [
            'title' => 'Configuration du nœud',
        ],
        'identity' => [
            'title' => 'Identité',
            'description' => 'Informations de base sur le nœud.',
        ],
        'connection' => [
            'title' => 'Détails de connexion',
            'description' => 'Configurez comment vous connecter à ce nœud.',
        ],
        'resources' => [
            'title' => 'Allocation des ressources',
            'description' => 'Définissez les limites de mémoire et de disque pour ce nœud.',
        ],
        'daemon' => [
            'title' => 'Configuration du démon',
            'description' => 'Configurez les paramètres spécifiques au démon.',
        ],
        'configuration' => [
            'title' => 'Configuration',
            'config_description' => 'Fichier de configuration',
            'deploy_description' => 'Générez une commande de déploiement personnalisée qui peut être utilisée pour configurer l\'agent sur le serveur cible.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Publique',
            'helper' => 'En définissant un nœud sur privé, vous refuserez la possibilité de se déployer automatiquement sur ce nœud.',
        ],
        'name' => [
            'label' => 'Nom',
            'placeholder' => 'Nom du nœud',
            'helper' => 'Un nom descriptif pour ce nœud.',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Description du nœud',
            'helper' => 'Description facultative pour ce nœud.',
        ],
        'location' => [
            'label' => 'Emplacement',
            'helper' => 'L\'emplacement auquel ce nœud est affecté.',
        ],
        'fqdn' => [
            'label' => 'Nom de domaine complet',
            'placeholder' => 'noeud.exemple.com',
            'helper' => 'Nom de domaine complet ou adresse IP.',
        ],
        'ssl' => [
            'label' => 'Utilise SSL',
            'helper' => 'Indique si le démon sur ce nœud est configuré pour utiliser SSL pour une communication sécurisée.',
            'helper_forced' => 'Ce panneau fonctionne sur HTTPS, donc SSL est forcé pour ce nœud.',
        ],
        'behind_proxy' => [
            'label' => 'Derrière le proxy',
            'helper' => 'Activez si ce nœud est derrière un proxy comme Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Mode d\'entretien',
            'helper' => 'Empêchez la création de nouveaux serveurs sur ce nœud.',
        ],
        'memory' => [
            'label' => 'Mémoire totale',
            'helper' => 'Mémoire totale en MiB disponible sur ce nœud.',
        ],
        'memory_overallocate' => [
            'label' => 'Surallocation de mémoire',
            'helper' => 'Pourcentage de mémoire à surallouer. Utilisez -1 pour désactiver la vérification.',
        ],
        'disk' => [
            'label' => 'Espace disque total',
            'helper' => 'Espace disque total en MiB disponible sur ce nœud.',
        ],
        'disk_overallocate' => [
            'label' => 'Surallocation de disque',
            'helper' => 'Pourcentage de disque à surallouer. Utilisez -1 pour désactiver la vérification.',
        ],
        'upload_size' => [
            'label' => 'Taille maximale de téléchargement',
            'helper' => 'Taille maximale de téléchargement de fichier autorisée via le panneau Web.',
        ],
        'daemon_base' => [
            'label' => 'Répertoire de base',
            'placeholder' => '/home/fichiers-démon',
            'helper' => 'Répertoire où sont stockés les fichiers du serveur.',
        ],
        'daemon_listen' => [
            'label' => 'Port démon',
            'helper' => 'Le port sur lequel le démon écoute la communication HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'Port SFTP',
            'helper' => 'Le port utilisé pour les connexions SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'ID de jeton',
        ],
        'container_text' => [
            'label' => 'Préfixe du conteneur',
            'helper' => 'Préfixe textuel affiché dans les noms des conteneurs.',
        ],
    ],

    'table' => [
        'health' => 'Santé',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'vérifier la console du navigateur',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Nom',
        'location' => 'Emplacements',
        'fqdn' => 'nom de domaine complet (FQDN)',
        'scheme' => 'Protocole',
        'public' => 'Publique',
        'behind_proxy' => 'Derrière le proxy',
        'maintenance_mode' => 'Maitenance',
        'memory' => 'Mémoire',
        'memory_overallocate' => 'Mémoire dépassée',
        'disk' => 'Disque',
        'disk_overallocate' => 'Disque dépassée',
        'upload_size' => 'Taille du fichier à télécharger',
        'daemon_listen' => 'Port démon',
        'daemon_sftp' => 'Port SFTP',
        'daemon_base' => 'Créer un dossier',
        'servers' => 'Serveurs',
        'created' => 'Créé',
        'updated' => 'Mise à jour',
    ],

    'filters' => [
        'public' => 'Publique',
        'maintenance' => 'Entretien',
        'public_true' => 'Publique',
        'public_false' => 'Privé',
        'maintenance_true' => 'En maintenance',
        'maintenance_false' => 'Actif',
    ],

    'actions' => [
        'create' => 'Créer',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'view' => 'Afficher',
        'random' => 'Aléatoire',
        'view_monitoring' => 'Voir la surveillance',
    ],

    'deployment' => [
        'generate_label' => 'Générer un jeton de déploiement',
        'modal_heading' => 'Commande de déploiement automatique',
        'modal_description' => 'Exécutez cette commande sur votre nœud pour configurer automatiquement l\'agent.',
        'modal_close' => 'Fermer',
        'command_label' => 'Commande de déploiement',
        'command_helper' => 'Copiez et exécutez cette commande sur votre serveur de nœuds.',
        'token_success' => 'Jeton généré avec succès',
        'token_success_body' => 'Copiez et exécutez la commande ci-dessous sur votre nœud.',
        'save_first' => 'Veuillez d\'abord enregistrer le nœud.',
        'auto_generated_key' => 'Clé de déploiement de nœud générée automatiquement.',
        'error' => 'Erreur lors de la génération du jeton. Veuillez réessayer.',
    ],

    'general' => [
        'na' => 'N / A',
        'unavailable' => 'Indisponible',
    ],

    'messages' => [
        'created' => 'Le nœud a été créé avec succès.',
        'updated' => 'Le nœud a été mis à jour avec succès.',
        'deleted' => 'Le nœud a été supprimé avec succès.',
        'cannot_delete_with_servers' => 'Impossible de supprimer un nœud avec des serveurs actifs.',
    ],

    'allocations' => [
        'label' => 'Allocations',
        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'server' => 'Serveurs',
            'notes' => 'Remarques',
            'created' => 'Créé',
            'unassigned' => 'Non attribué',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'Adresse IP',
                'helper' => 'Prend en charge une seule adresse IP ou CIDR (par exemple 192.0.2.1 ou 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Port',
                'helper' => 'Entrez les ports ou les plages (par exemple 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'Protocole Internet',
                'helper' => 'Alias facultatif à afficher à la place de l\'adresse IP.',
            ],
        ],
        'actions' => [
            'add' => 'Ajouter une allocation',
            'delete' => 'Supprimer',
        ],
        'messages' => [
            'created' => 'Allocation ajoutée.',
            'deleted' => 'Allocation supprimée.',
            'failed' => 'Échec de l\'action d\'allocation.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'Le FQDN ou l\'adresse IP fourni ne résout pas en une adresse IP valide.',
        'fqdn_required_for_ssl' => 'Un nom de domaine complet qui résout une adresse IP publique est nécessaire pour utiliser SSL pour ce noeud.',
    ],
    'notices' => [
        'allocations_added' => 'Les allocations ont été ajoutées avec succès à ce noeud.',
        'node_deleted' => 'Le noeud a été supprimé du panneau.',
        'location_required' => 'Vous devez avoir configuré au moins un emplacement avant de pouvoir ajouter un noeud au panel.',
        'node_created' => 'Nouveau nœud créé avec succès. Vous pouvez configurer automatiquement le démon sur cette machine en vous rendant dans l\'onglet « Configuration ». Avant de pouvoir ajouter des serveurs, vous devez d\'abord attribuer au moins une adresse IP et un port.',
        'node_updated' => 'Les informations relatives aux noeuds ont été mises à jour. Si des paramètres du daemon ont été modifiés, vous devrez le redémarrer pour que ces modifications prennent effet.',
        'unallocated_deleted' => 'Suppression de tous les ports non attribués pour <code>:ip</code>.',
    ],
];
