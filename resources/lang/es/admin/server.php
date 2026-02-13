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
        'no_new_default_allocation' => 'Estás intentando eliminar la asignación predeterminada para este servidor pero no hay asignación de respaldo disponible.',
        'marked_as_failed' => 'Este servidor fue marcado como fallido en una instalación anterior. El estado actual no puede cambiarse en este estado.',
        'bad_variable' => 'Hubo un error de validación con la variable :name.',
        'daemon_exception' => 'Hubo una excepción al intentar comunicarse con el daemon resultando en un código de respuesta HTTP/:code. Esta excepción ha sido registrada. (id de solicitud: :request_id)',
        'default_allocation_not_found' => 'La asignación predeterminada solicitada no se encontró en las asignaciones de este servidor.',
    ],

    'alerts' => [
        'install_toggled' => 'El estado de instalación de este servidor ha sido alternado.',
        'server_suspended' => 'Server has been :action.',
        'server_reinstalled' => 'Este servidor ha sido puesto en cola para una reinstalación comenzando ahora.',
        'server_deleted' => 'El servidor ha sido eliminado exitosamente del sistema.',
        'server_delete_failed' => 'Failed to delete server.',
        'startup_changed' => 'La configuración de inicio de este servidor ha sido actualizada. Si el nido o egg de este servidor fue cambiado, se realizará una reinstalación ahora.',
        'server_created' => 'El servidor fue creado exitosamente en el panel. Por favor permite al daemon unos minutos para instalar completamente este servidor.',
        'build_updated' => 'Los detalles de construcción de este servidor han sido actualizados. Algunos cambios pueden requerir un reinicio para surtir efecto.',
        'suspension_toggled' => 'El estado de suspensión del servidor ha sido cambiado a :status.',
        'rebuild_on_boot' => 'Este servidor ha sido marcado como que requiere una reconstrucción del contenedor Docker. Esto ocurrirá la próxima vez que el servidor se inicie.',
        'details_updated' => 'Los detalles del servidor han sido actualizados exitosamente.',
        'docker_image_updated' => 'Se cambió exitosamente la imagen Docker predeterminada para este servidor. Se requiere un reinicio para aplicar este cambio.',
        'node_required' => 'Debes tener al menos un nodo configurado antes de poder añadir un servidor a este panel.',
        'transfer_nodes_required' => 'Debes tener al menos dos nodos configurados antes de poder transferir servidores.',
        'transfer_started' => 'La transferencia del servidor ha comenzado.',
        'transfer_not_viable' => 'El nodo seleccionado no tiene el espacio en disco o memoria disponible requerida para acomodar este servidor.',
    ],
];
