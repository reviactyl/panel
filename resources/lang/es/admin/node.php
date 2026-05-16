<?php

return [
    'label' => 'Nodo',
    'plural-label' => 'Nodos',

    'sections' => [
        'overview' => [
            'title' => 'Descripción general',
            'information-label' => 'Información del nodo',
            'version-label' => 'Versión del agente',
            'architecture-label' => 'Arquitectura',
            'kernel-label' => 'Núcleo',
            'cpus-label' => 'Hilos de CPU',
            'cpu-usage-label' => 'Uso de CPU',
            'memory-usage-label' => 'Uso de la memoria',
            'disk-usage-label' => 'Uso del disco',
        ],
        'tabs' => [
            'title' => 'Configuración de nodo',
        ],
        'identity' => [
            'title' => 'Identidad',
            'description' => 'Información básica del nodo.',
        ],
        'connection' => [
            'title' => 'Detalles de conexión',
            'description' => 'Configure cómo conectarse a este nodo.',
        ],
        'resources' => [
            'title' => 'Asignación de recursos',
            'description' => 'Defina los límites de memoria y disco para este nodo.',
        ],
        'daemon' => [
            'title' => 'Configuración del demonio',
            'description' => 'Configure ajustes específicos del demonio.',
        ],
        'configuration' => [
            'title' => 'Configuración',
            'config_description' => 'Archivo de configuración',
            'deploy_description' => 'Genere un comando de implementación personalizado que se pueda utilizar para configurar el Agente en el servidor de destino.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Público',
            'helper' => 'Al configurar un nodo como privado, negará la capacidad de realizar una implementación automática en este nodo.',
        ],
        'name' => [
            'label' => 'Nombre',
            'placeholder' => 'Nombre de nodo',
            'helper' => 'Un nombre descriptivo para este nodo.',
        ],
        'description' => [
            'label' => 'Descripción',
            'placeholder' => 'Descripción del nodo',
            'helper' => 'Descripción opcional para este nodo.',
        ],
        'location' => [
            'label' => 'Ubicación',
            'helper' => 'La ubicación a la que está asignado este nodo.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'nodo.ejemplo.com',
            'helper' => 'Nombre de dominio completo o dirección IP.',
        ],
        'ssl' => [
            'label' => 'Utiliza SSL',
            'helper' => 'Si el demonio de este nodo está configurado para utilizar SSL para una comunicación segura.',
            'helper_forced' => 'Este panel se ejecuta en HTTPS, por lo que se fuerza SSL para este nodo.',
        ],
        'behind_proxy' => [
            'label' => 'Detrás del proxy',
            'helper' => 'Habilítelo si este nodo está detrás de un proxy como Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Modo de mantenimiento',
            'helper' => 'Evite que se creen nuevos servidores en este nodo.',
        ],
        'memory' => [
            'label' => 'Memoria Total',
            'helper' => 'Memoria total en MiB disponible en este nodo.',
        ],
        'memory_overallocate' => [
            'label' => 'Sobreasignación de memoria',
            'helper' => 'Porcentaje de memoria para sobreasignar. Utilice -1 para desactivar la comprobación.',
        ],
        'disk' => [
            'label' => 'Espacio total en disco',
            'helper' => 'Espacio total en disco en MiB disponible en este nodo.',
        ],
        'disk_overallocate' => [
            'label' => 'Sobreasignación de disco',
            'helper' => 'Porcentaje de disco que se sobreasignará. Utilice -1 para desactivar la comprobación.',
        ],
        'upload_size' => [
            'label' => 'Tamaño máximo de carga',
            'helper' => 'Tamaño máximo de carga de archivos permitido a través del panel web.',
        ],
        'daemon_base' => [
            'label' => 'Directorio base',
            'helper' => 'Directorio donde se almacenan los archivos del servidor.',
        ],
        'daemon_listen' => [
            'label' => 'Puerto demonio',
            'helper' => 'El puerto en el que el demonio escucha la comunicación HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'Puerto SFTP',
            'helper' => 'El puerto utilizado para las conexiones SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'ID de token',
        ],
        'container_text' => [
            'label' => 'Prefijo de contenedor',
            'helper' => 'Prefijo de texto mostrado en los nombres de los contenedores.',
        ],
    ],

    'table' => [
        'health' => 'Salud',
        'health_http_status' => 'HTTP :status',
        'health_check_console' => 'comprobar la consola del navegador',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Nombre',
        'location' => 'Ubicación',
        'fqdn' => 'FQDN',
        'scheme' => 'Protocolo',
        'public' => 'Público',
        'behind_proxy' => 'Detrás del proxy',
        'maintenance_mode' => 'Mantenimiento',
        'memory' => 'Memoria',
        'memory_overallocate' => 'Memoria terminada',
        'disk' => 'Disco',
        'disk_overallocate' => 'Disco terminado',
        'upload_size' => 'Tamaño de carga',
        'daemon_listen' => 'Puerto demonio',
        'daemon_sftp' => 'Puerto SFTP',
        'daemon_base' => 'Directorio base',
        'servers' => 'Servidores',
        'created' => 'Creado',
        'updated' => 'Actualizado',
    ],

    'filters' => [
        'public' => 'Público',
        'maintenance' => 'Mantenimiento',
        'public_true' => 'Público',
        'public_false' => 'Privado',
        'maintenance_true' => 'En mantenimiento',
        'maintenance_false' => 'Activo',
    ],

    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar',
        'delete' => 'Borrar',
        'view' => 'Vista',
        'random' => 'Aleatorio',
        'view_monitoring' => 'Ver monitoreo',
    ],

    'deployment' => [
        'generate_label' => 'Generar token de implementación',
        'modal_heading' => 'Comando de implementación automática',
        'modal_description' => 'Ejecute este comando en su nodo para configurar automáticamente el Agente.',
        'modal_close' => 'Cerca',
        'command_label' => 'Comando de implementación',
        'command_helper' => 'Copie y ejecute este comando en su servidor de nodo.',
        'token_success' => 'Token generado con éxito',
        'token_success_body' => 'Copie y ejecute el siguiente comando en su nodo.',
        'save_first' => 'Primero guarde el nodo.',
        'auto_generated_key' => 'Clave de implementación de nodo generada automáticamente.',
        'error' => 'Error al generar el token. Por favor inténtalo de nuevo.',
    ],

    'general' => [
        'na' => 'N / A',
        'unavailable' => 'Indisponible',
    ],

    'messages' => [
        'created' => 'El nodo se ha creado correctamente.',
        'updated' => 'El nodo se ha actualizado correctamente.',
        'deleted' => 'El nodo se ha eliminado correctamente.',
        'cannot_delete_with_servers' => 'No se puede eliminar un nodo con servidores activos.',
    ],

    'allocations' => [
        'label' => 'Asignaciones',
        'table' => [
            'ip' => 'IP',
            'port' => 'Puerto',
            'alias' => 'Alias',
            'server' => 'Servidor',
            'notes' => 'Notas',
            'created' => 'Creado',
            'unassigned' => 'No asignado',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Address',
                'helper' => 'Admite una única IP o CIDR (por ejemplo, 192.0.2.1 o 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Puertos',
                'helper' => 'Ingrese puertos o rangos (por ejemplo, 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'Alias ​​de IP',
                'helper' => 'Alias ​​opcional para mostrar en lugar de la IP.',
            ],
        ],
        'actions' => [
            'add' => 'Agregar asignación',
            'delete' => 'Borrar',
        ],
        'messages' => [
            'created' => 'Se agregaron asignaciones.',
            'deleted' => 'Asignación eliminada.',
            'failed' => 'La acción de asignación falló.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'El FQDN o dirección IP proporcionada no resuelve a una dirección IP válida.',
        'fqdn_required_for_ssl' => 'Se requiere un nombre de dominio completamente cualificado que resuelva a una dirección IP pública para usar SSL en este nodo.',
    ],
    'notices' => [
        'allocations_added' => 'Las asignaciones se han añadido exitosamente a este nodo.',
        'node_deleted' => 'El nodo ha sido eliminado exitosamente del panel.',
        'location_required' => 'Debes tener al menos una ubicación configurada antes de poder añadir un nodo a este panel.',
        'node_created' => 'Nuevo nodo creado correctamente. Puede configurar automáticamente el demonio en esta máquina visitando la pestaña \'Configuración\'. Antes de poder agregar servidores, primero debe asignar al menos una dirección IP y un puerto.',
        'node_updated' => 'La información del nodo ha sido actualizada. Si se cambiaron configuraciones del daemon, necesitarás reiniciarlo para que los cambios surtan efecto.',
        'unallocated_deleted' => 'Se eliminaron todos los puertos no asignados para <code>:ip</code>.',
    ],
];
