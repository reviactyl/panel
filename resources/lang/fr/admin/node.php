<?php

return [
    'label' => 'Node',
    'plural-label' => 'Nodes',

    'sections' => [
        'identity' => [
            'title' => 'Identity',
            'description' => 'Basic node information.',
        ],
        'connection' => [
            'title' => 'Connection Details',
            'description' => 'Configure how to connect to this node.',
        ],
        'resources' => [
            'title' => 'Resource Allocation',
            'description' => 'Define memory and disk limits for this node.',
        ],
        'daemon' => [
            'title' => 'Daemon Configuration',
            'description' => 'Configure daemon-specific settings.',
        ],
        'configuration' => [
            'title' => 'Configuration',
            'config_description' => 'Configuration File',
            'deploy_description' => 'Generate a custom deployment command that can be used to configure Wings on the target server.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Public',
            'helper' => 'By setting a node to private you will be denying the ability to auto-deploy to this node. ',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Node Name',
            'helper' => 'A descriptive name for this node.',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Node description',
            'helper' => 'Optional description for this node.',
        ],
        'location' => [
            'label' => 'Location',
            'helper' => 'The location this node is assigned to.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'Fully qualified domain name or IP address.',
        ],
        'ssl' => [
            'label' => 'Uses SSL',
            'helper' => 'Whether the daemon on this node is configured to use SSL for secure communication.',
            'helper_forced' => 'This panel is running on HTTPS, so SSL is forced for this node.',
        ],
        'behind_proxy' => [
            'label' => 'Behind Proxy',
            'helper' => 'Enable if this node is behind a proxy like Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Maintenance Mode',
            'helper' => 'Prevent new servers from being created on this node.',
        ],
        'memory' => [
            'label' => 'Total Memory',
            'helper' => 'Total memory in MiB available on this node.',
        ],
        'memory_overallocate' => [
            'label' => 'Memory Overallocation',
            'helper' => 'Percentage of memory to overallocate. Use -1 to disable checking.',
        ],
        'disk' => [
            'label' => 'Total Disk Space',
            'helper' => 'Total disk space in MiB available on this node.',
        ],
        'disk_overallocate' => [
            'label' => 'Disk Overallocation',
            'helper' => 'Percentage of disk to overallocate. Use -1 to disable checking.',
        ],
        'upload_size' => [
            'label' => 'Max Upload Size',
            'helper' => 'Maximum file upload size allowed via the web panel.',
        ],
        'daemon_base' => [
            'label' => 'Base Directory',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Directory where server files are stored.',
        ],
        'daemon_listen' => [
            'label' => 'Daemon Port',
            'helper' => 'The port the daemon listens on for HTTP communication.',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP Port',
            'helper' => 'The port used for SFTP connections.',
        ],
        'daemon_token_id' => [
            'label' => 'Token ID',
        ],
        'container_text' => [
            'label' => 'Container Prefix',
            'helper' => 'Préfixe textuel affiché dans les noms des conteneurs.',
        ],
    ],

    'table' => [
        'health' => 'Health',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'check browser console',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Nom',
        'location' => 'Emplacements',
        'fqdn' => 'nom de domaine complet (FQDN)',
        'scheme' => 'Protocole',
        'public' => 'Public',
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
        'public' => 'Public',
        'maintenance' => 'Maintenance',
        'public_true' => 'Public',
        'public_false' => 'Private',
        'maintenance_true' => 'Under Maintenance',
        'maintenance_false' => 'Active',
    ],

    'actions' => [
        'create' => 'Créer',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'view' => 'Afficher',
    ],

    'deployment' => [
        'generate_label' => 'Generate Deployment Token',
        'modal_heading' => 'Auto-Deploy Command',
        'modal_description' => 'Run this command on your node to automatically configure Wings.',
        'modal_close' => 'Close',
        'command_label' => 'Deployment Command',
        'command_helper' => 'Copy and run this command on your node server.',
        'token_success' => 'Token Generated Successfully',
        'token_success_body' => 'Copy and run the command below on your node.',
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
