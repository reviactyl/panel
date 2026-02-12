<?php

return [
    'label' => 'Server',
    'plural-label' => 'Servers',

    'sections' => [
        'identity' => [
            'title' => 'Identity',
            'description' => 'Basic server information and ownership.',
        ],
        'allocation' => [
            'title' => 'Allocation',
            'description' => 'Select the node and network allocation for this server.',
        ],
        'startup' => [
            'title' => 'Startup',
            'description' => 'Configure the egg, startup command, and Docker image.',
        ],
        'resources' => [
            'title' => 'Resource Limits',
            'description' => 'Define the server resource limits.',
        ],
        'feature_limits' => [
            'title' => 'Feature Limits',
            'description' => 'Limit databases, allocations, and backups.',
        ],
        'environment' => [
            'title' => 'Environment Variables',
            'description' => 'Set environment values for the selected egg.',
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Advanced Mode',
            'helper' => 'Toggle to show additional server configuration options. Toggle on only if you understand the implications of the additional settings.',
        ],
        'external_id' => [
            'label' => 'External ID',
            'helper' => 'Optional unique identifier for this server.',
        ],
        'owner' => [
            'label' => 'Owner',
            'helper' => 'Select the user that owns this server.',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Server Name',
            'helper' => 'A short name for this server.',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Server description',
            'helper' => 'Optional description for this server.',
        ],
        'node' => [
            'label' => 'Node',
            'helper' => 'The node this server will be deployed to.',
        ],
        'allocation' => [
            'label' => 'Primary Allocation',
            'helper' => 'The default IP/port allocation for this server.',
        ],
        'additional_allocations' => [
            'label' => 'Additional Allocations',
            'helper' => 'Optional extra allocations to assign.',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'The service nest for this server.',
        ],
        'egg' => [
            'label' => 'Egg',
            'helper' => 'The egg that defines server behavior.',
        ],
        'startup' => [
            'label' => 'Startup Command',
            'helper' => 'The startup command for the server.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'placeholder' => 'e.g. ghcr.io/pterodactyl/yolks:java_17',
            'helper' => 'Docker image used to run this server.',
            'custom' => 'Custom',
        ],
        'skip_scripts' => [
            'label' => 'Skip Egg Scripts',
            'helper' => 'Skip egg install scripts during creation.',
        ],
        'start_on_completion' => [
            'label' => 'Start on Completion',
            'helper' => 'Automatically start the server after installation.',
        ],
        'memory' => [
            'label' => 'Memory',
            'helper' => 'Total memory allocation. Set to 0 for unlimited. (Unlimited Memory doesn\'t work for Minecraft Eggs due to Startup Command)',
        ],
        'swap' => [
            'label' => 'Swap',
            'helper' => 'Swap memory allocation. Set to 0 to disable swap or -1 to allow unlimited swap.',
        ],
        'disk' => [
            'label' => 'Disk',
            'helper' => 'Disk space allocation. Set to 0 for unlimited.',
        ],
        'io' => [
            'label' => 'IO Weight',
            'helper' => 'Relative disk I/O priority (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'CPU limit in percent. 100% means one full core, 200% means two full cores, etc.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Enter size in GiB',
            'helper' => 'You can enter sizes in GiB by using the "GiB" suffix (e.g. 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'CPU Threads',
            'helper' => 'Optional thread pinning. Example: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Disable OOM Killer',
            'helper' => 'Prevent the kernel from killing the process when out of memory.',
        ],
        'database_limit' => [
            'label' => 'Database Limit',
            'helper' => 'Maximum number of databases.',
        ],
        'allocation_limit' => [
            'label' => 'Allocation Limit',
            'helper' => 'Maximum number of allocations.',
        ],
        'backup_limit' => [
            'label' => 'Backup Limit',
            'helper' => 'Maximum number of backups.',
        ],
        'environment' => [
            'key' => 'Variable',
            'value' => 'Value',
            'helper' => 'Environment variables for this egg.',
        ],
        'use_custom_image' => [
            'label' => 'Use Custom Image',
            'helper' => 'Toggle to use a custom Docker image instead of one provided by the egg.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'owner' => 'Owner',
        'node' => 'Node',
        'allocation' => 'Allocation',
        'status' => 'Status',
        'egg' => 'Egg',
        'memory' => 'Memory',
        'disk' => 'Disk',
        'cpu' => 'CPU',
        'created' => 'Created',
        'updated' => 'Updated',
        'installed' => 'Installed',
        'no_status' => 'No Status',
    ],

    'messages' => [
        'created' => 'Server has been successfully created.',
        'updated' => 'Server has been successfully updated.',
        'deleted' => 'Server has been successfully deleted.',
    ],

    'actions' => [
        'edit' => 'Edit',
        'toggle_install_status' => 'Toggle Install Status',
        'suspend' => 'Suspend',
        'unsuspend' => 'Unsuspend',
        'suspended' => 'Suspended',
        'unsuspended' => 'Unsuspended',
        'reinstall' => 'Reinstall',
        'delete' => 'Delete',
        'delete_forcibly' => 'Forcibly Delete',
        'view' => 'View',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'ನೀವು ಈ ಸರ್ವರ್‌ನ ಡೀಫಾಲ್ಟ್ ಅಲೊಕೆಷನ್ ಅನ್ನು ಅಳಿಸಲು ಪ್ರಯತ್ನಿಸುತ್ತಿದ್ದೀರಿ, ಆದರೆ ಬಳಸಲು ಯಾವುದೇ ಪರ್ಯಾಯ ಅಲೊಕೆಷನ್ ಲಭ್ಯವಿಲ್ಲ.',
        'marked_as_failed' => 'ಈ ಸರ್ವರ್ ಹಿಂದಿನ ಸ್ಥಾಪನೆಯಲ್ಲಿ ವಿಫಲವಾಗಿದೆ ಎಂದು ಗುರುತಿಸಲಾಗಿದೆ. ಈ ಸ್ಥಿತಿಯಲ್ಲಿ ಪ್ರಸ್ತುತ ಸ್ಥಿತಿಯನ್ನು ಬದಲಾಯಿಸಲು ಸಾಧ್ಯವಿಲ್ಲ.',
        'bad_variable' => ':name ವೇರಿಯಬಲ್‌ನಲ್ಲಿ ಮಾನ್ಯತೆ ದೋಷ ಕಂಡುಬಂದಿದೆ.',
        'daemon_exception' => 'ಡೀಮನ್ ಜೊತೆಗೆ ಸಂವಹನ ಮಾಡಲು ಪ್ರಯತ್ನಿಸುವಾಗ ಒಂದು ಎಕ್ಸೆಪ್ಷನ್ ಸಂಭವಿಸಿದೆ, ಇದರಿಂದ HTTP/:code ಪ್ರತಿಕ್ರಿಯಾ ಕೋಡ್ ಬಂದಿದೆ. ಈ ಎಕ್ಸೆಪ್ಷನ್ ಲಾಗ್ ಮಾಡಲಾಗಿದೆ. (ವಿನಂತಿ ಐಡಿ: :request_id)',
        'default_allocation_not_found' => 'ಕೋರಿದ ಡೀಫಾಲ್ಟ್ ಅಲೊಕೆಷನ್ ಈ ಸರ್ವರ್‌ನ ಅಲೊಕೆಷನ್‌ಗಳಲ್ಲಿ ಕಂಡುಬಂದಿಲ್ಲ.',
    ],

    'alerts' => [
        'install_toggled' => 'ಈ ಸರ್ವರ್‌ನ ಸ್ಥಾಪನಾ ಸ್ಥಿತಿಯನ್ನು ಬದಲಾಯಿಸಲಾಗಿದೆ.',
        'server_suspended' => 'Server has been :action.',
        'server_reinstalled' => 'ಈ ಸರ್ವರ್ ಅನ್ನು ಮರುಸ್ಥಾಪನೆಗಾಗಿ ಸಾಲಿನಲ್ಲಿ ಸೇರಿಸಲಾಗಿದೆ ಮತ್ತು ಪ್ರಕ್ರಿಯೆ ಈಗ ಆರಂಭವಾಗುತ್ತದೆ.',
        'server_deleted' => 'ಸರ್ವರ್ ಅನ್ನು ವ್ಯವಸ್ಥೆಯಿಂದ ಯಶಸ್ವಿಯಾಗಿ ಅಳಿಸಲಾಗಿದೆ.',
        'server_delete_failed' => 'Failed to delete server.',
        'startup_changed' => 'ಈ ಸರ್ವರ್‌ನ ಸ್ಟಾರ್ಟ್‌ಅಪ್ ಸಂರಚನೆಯನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ. ಈ ಸರ್ವರ್‌ನ ನೆಸ್ಟ್ ಅಥವಾ ಎಗ್ ಬದಲಾಗಿದ್ದರೆ, ಈಗ ಮರುಸ್ಥಾಪನೆ ನಡೆಯಲಿದೆ.',
        'server_created' => 'ಸರ್ವರ್ ಅನ್ನು ಪ್ಯಾನೆಲ್‌ನಲ್ಲಿ ಯಶಸ್ವಿಯಾಗಿ ರಚಿಸಲಾಗಿದೆ. ಈ ಸರ್ವರ್ ಅನ್ನು ಸಂಪೂರ್ಣವಾಗಿ ಸ್ಥಾಪಿಸಲು ಡೀಮನ್‌ಗೆ ಕೆಲವು ನಿಮಿಷಗಳ ಸಮಯ ನೀಡಿ.',
        'build_updated' => 'ಈ ಸರ್ವರ್‌ನ ಬಿಲ್ಡ್ ವಿವರಗಳನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ. ಕೆಲವು ಬದಲಾವಣೆಗಳು ಪರಿಣಾಮಕಾರಿಯಾಗಲು ಮರುಪ್ರಾರಂಭ ಅಗತ್ಯವಿರಬಹುದು.',
        'suspension_toggled' => 'ಸರ್ವರ್ ಸ್ಥಗಿತ ಸ್ಥಿತಿಯನ್ನು :status ಗೆ ಬದಲಾಯಿಸಲಾಗಿದೆ.',
        'rebuild_on_boot' => 'ಈ ಸರ್ವರ್‌ಗೆ ಡಾಕರ್ ಕಂಟೈನರ್ ಮರುನಿರ್ಮಾಣ ಅಗತ್ಯವಿದೆ ಎಂದು ಗುರುತಿಸಲಾಗಿದೆ. ಮುಂದಿನ ಬಾರಿ ಸರ್ವರ್ ಪ್ರಾರಂಭಿಸಿದಾಗ ಇದು ನಡೆಯಲಿದೆ.',
        'details_updated' => 'ಸರ್ವರ್ ವಿವರಗಳನ್ನು ಯಶಸ್ವಿಯಾಗಿ ನವೀಕರಿಸಲಾಗಿದೆ.',
        'docker_image_updated' => 'ಈ ಸರ್ವರ್‌ಗಾಗಿ ಬಳಸುವ ಡೀಫಾಲ್ಟ್ ಡಾಕರ್ ಇಮೇಜ್ ಅನ್ನು ಯಶಸ್ವಿಯಾಗಿ ಬದಲಾಯಿಸಲಾಗಿದೆ. ಈ ಬದಲಾವಣೆಯನ್ನು ಅನ್ವಯಿಸಲು ಮರುಪ್ರಾರಂಭ ಅಗತ್ಯವಿದೆ.',
        'node_required' => 'ಈ ಪ್ಯಾನೆಲ್‌ಗೆ ಸರ್ವರ್ ಸೇರಿಸಲು ಕನಿಷ್ಠ ಒಂದು ನೋಡ್ ಸಂರಚಿಸಿರಬೇಕು.',
        'transfer_nodes_required' => 'ಸರ್ವರ್‌ಗಳನ್ನು ವರ್ಗಾಯಿಸಲು ಕನಿಷ್ಠ ಎರಡು ನೋಡ್‌ಗಳನ್ನು ಸಂರಚಿಸಿರಬೇಕು.',
        'transfer_started' => 'ಸರ್ವರ್ ವರ್ಗಾವಣೆ ಪ್ರಾರಂಭವಾಗಿದೆ.',
        'transfer_not_viable' => 'ನೀವು ಆಯ್ಕೆ ಮಾಡಿದ ನೋಡ್‌ಗೆ ಈ ಸರ್ವರ್‌ಗೆ ಅಗತ್ಯವಿರುವ ಡಿಸ್ಕ್ ಸ್ಥಳ ಅಥವಾ ಮೆಮೊರಿ ಲಭ್ಯವಿಲ್ಲ.',
    ],
];
