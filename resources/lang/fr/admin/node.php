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
            'helper' => 'Text prefix displayed in container names.',
        ],
        'daemon_text' => [
            'label' => 'Daemon Prefix',
            'helper' => 'Text prefix displayed in daemon logs.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Name',
        'location' => 'Location',
        'fqdn' => 'FQDN',
        'scheme' => 'Protocol',
        'public' => 'Public',
        'behind_proxy' => 'Behind Proxy',
        'maintenance_mode' => 'Maintenance',
        'memory' => 'Memory',
        'memory_overallocate' => 'Memory Over',
        'disk' => 'Disk',
        'disk_overallocate' => 'Disk Over',
        'upload_size' => 'Upload Size',
        'daemon_listen' => 'Daemon Port',
        'daemon_sftp' => 'SFTP Port',
        'daemon_base' => 'Base Directory',
        'servers' => 'Servers',
        'created' => 'Created',
        'updated' => 'Updated',
    ],

    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
    ],

    'messages' => [
        'created' => 'Node has been successfully created.',
        'updated' => 'Node has been successfully updated.',
        'deleted' => 'Node has been successfully deleted.',
        'cannot_delete_with_servers' => 'Cannot delete a node with active servers.',
    ],

    'allocations' => [
        'label' => 'Allocations',
        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'server' => 'Server',
            'notes' => 'Notes',
            'created' => 'Created',
            'unassigned' => 'Unassigned',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Address',
                'helper' => 'Supports single IP or CIDR (e.g. 192.0.2.1 or 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Ports',
                'helper' => 'Enter ports or ranges (e.g. 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP Alias',
                'helper' => 'Optional alias to display instead of the IP.',
            ],
        ],
        'actions' => [
            'add' => 'Add Allocation',
            'delete' => 'Delete',
        ],
        'messages' => [
            'created' => 'Allocations added.',
            'deleted' => 'Allocation deleted.',
            'failed' => 'Allocation action failed.',
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
        'node_created' => 'Nouveau noeud créé avec succès. Vous pouvez configurer automatiquement le daemon sur cette machine en vous rendant dans l\'onglet \'Configuration\'. <strong>Avant de pouvoir ajouter des serveurs, vous devez d\'abord attribuer au moins une adresse IP et un port. </strong>',
        'node_updated' => 'Les informations relatives aux noeuds ont été mises à jour. Si des paramètres du daemon ont été modifiés, vous devrez le redémarrer pour que ces modifications prennent effet.',
        'unallocated_deleted' => 'Suppression de tous les ports non attribués pour <code>:ip</code>.',
    ],
];
