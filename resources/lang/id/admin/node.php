<?php

return [
    'label' => 'Node',
    'plural-label' => 'Nodes',

    'sections' => [
        'overview' => [
            'title' => 'Overview',
            'information-label' => 'Node Information',
            'version-label' => 'Agent Version',
            'architecture-label' => 'Architecture',
            'kernel-label' => 'Kernel',
            'cpus-label' => 'CPU Threads',
            'cpu-usage-label' => 'CPU Usage',
            'memory-usage-label' => 'Memory Usage',
            'disk-usage-label' => 'Disk Usage',
        ],
        'tabs' => [
            'title' => 'Node Configuration',
        ],
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
            'deploy_description' => 'Generate a custom deployment command that can be used to configure Agent on the target server.',
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
    ],

    'table' => [
        'health' => 'Health',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'check browser console',
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

    'filters' => [
        'public' => 'Public',
        'maintenance' => 'Maintenance',
        'public_true' => 'Public',
        'public_false' => 'Private',
        'maintenance_true' => 'Under Maintenance',
        'maintenance_false' => 'Active',
    ],

    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'random' => 'Random',
        'view_monitoring' => 'View Monitoring',
    ],

    'deployment' => [
        'generate_label' => 'Generate Deployment Token',
        'modal_heading' => 'Auto-Deploy Command',
        'modal_description' => 'Run this command on your node to automatically configure Agent.',
        'modal_close' => 'Close',
        'command_label' => 'Deployment Command',
        'command_helper' => 'Copy and run this command on your node server.',
        'token_success' => 'Token Generated Successfully',
        'token_success_body' => 'Copy and run the command below on your node.',
        'save_first' => 'Please save the node first.',
        'auto_generated_key' => 'Automatically generated node deployment key.',
        'error' => 'Error generating token. Please try again.',
    ],

    'general' => [
        'na' => 'N/A',
        'unavailable' => 'Unavailable',
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
        'fqdn_not_resolvable' => 'FQDN atau alamat IP yang diberikan tidak dapat diselesaikan ke alamat IP yang valid.',
        'fqdn_required_for_ssl' => 'Nama domain yang sepenuhnya memenuhi syarat (FQDN) yang merujuk ke alamat IP publik diperlukan untuk menggunakan SSL pada node ini.',
    ],
    'notices' => [
        'allocations_added' => 'Alokasi telah berhasil ditambahkan ke node ini.',
        'node_deleted' => 'Node telah berhasil dihapus dari panel.',
        'location_required' => 'Anda harus memiliki setidaknya satu lokasi yang dikonfigurasi sebelum Anda dapat menambahkan node ke panel ini.',
        'node_created' => 'Successfully created new node. You can automatically configure the daemon on this machine by visiting the \'Configuration\' tab. Before you can add any servers you must first allocate at least one IP address and port.',
        'node_updated' => 'Informasi node telah diperbarui. Jika pengaturan daemon diubah, Anda perlu me-rebootnya agar perubahan tersebut berlaku.',
        'unallocated_deleted' => 'Menghapus semua port yang tidak dialokasikan untuk <code>:ip</code>.',
    ],
];
