<?php

return [
    'label' => 'Servidor',
    'plural-label' => 'Servidores',

    'sections' => [
        'identity' => [
            'title' => 'Identidad',
            'description' => 'Información básica del servidor y propiedad.',
        ],
        'allocation' => [
            'title' => 'Asignación',
            'description' => 'Seleccione el nodo y la asignación de red para este servidor.',
        ],
        'startup' => [
            'title' => 'Puesta en marcha',
            'description' => 'Configure el huevo, el comando de inicio y la imagen de Docker.',
        ],
        'resources' => [
            'title' => 'Límites de recursos',
            'description' => 'Defina los límites de recursos del servidor.',
        ],
        'feature_limits' => [
            'title' => 'Límites de funciones',
            'description' => 'Limite las bases de datos, las asignaciones y las copias de seguridad.',
        ],
        'environment' => [
            'title' => 'Variables de entorno',
            'description' => 'Establezca valores ambientales para el huevo seleccionado.',
        ],
    ],

    'status' => [
        'online' => 'En línea',
        'offline' => 'Desconectado',
        'starting' => 'A partir de',
        'stopping' => 'Parada',
        'crashed' => 'Se estrelló',
        'installing' => 'Instalación',
        'restoring_backup' => 'Restaurar copia de seguridad',
        'install_failed' => 'Instalación fallida',
        'reinstall_failed' => 'Reinstalación fallida',
        'suspended' => 'Suspendido',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Detalles principales',
            'allocation' => 'Gestión de asignaciones',
            'feature_limits' => 'Límites de las funciones de la aplicación',
            'resources' => 'Gestión de recursos',
            'nest' => 'Configuración de nido',
            'docker' => 'Configuración de la ventana acoplable',
            'startup' => 'Configuración de inicio',
            'variables' => 'Variables de servicio',
        ],

        'fields' => [
            'name' => [
                'label' => 'Nombre del servidor',
                'placeholder' => 'Nombre del servidor',
                'helper' => 'Límites de caracteres: a-z A-Z 0-9 _ - . y espacios.',
            ],
            'owner' => [
                'label' => 'Propietario del servidor',
                'helper' => 'Dirección de correo electrónico del propietario del servidor.',
            ],
            'description' => [
                'label' => 'Descripción del servidor',
                'helper' => 'Una breve descripción de este servidor.',
            ],
            'start_on_completion' => [
                'label' => 'Iniciar el servidor cuando esté instalado',
            ],
            'node' => [
                'label' => 'Nodo',
                'helper' => 'El nodo en el que se implementará este servidor.',
            ],
            'allocation' => [
                'label' => 'Asignación predeterminada',
                'helper' => 'La asignación principal que se asignará a este servidor.',
            ],
            'additional_allocations' => [
                'label' => 'Asignaciones adicionales',
                'helper' => 'Asignaciones adicionales para asignar a este servidor en el momento de la creación.',
            ],
            'database_limit' => [
                'label' => 'Límite de base de datos',
                'helper' => 'El número total de bases de datos que un usuario puede crear para este servidor.',
            ],
            'allocation_limit' => [
                'label' => 'Límite de asignación',
                'helper' => 'El número total de asignaciones que un usuario puede crear para este servidor.',
            ],
            'backup_limit' => [
                'label' => 'Límite de copia de seguridad',
                'helper' => 'El número total de copias de seguridad que se pueden crear para este servidor.',
            ],
            'cpu' => [
                'label' => 'Límite de CPU',
                'helper' => 'Establezca 0 para que no haya límite de CPU. Un núcleo virtual completo es del 100%.',
            ],
            'threads' => [
                'label' => 'Fijación de CPU',
                'helper' => 'Avanzado: use un solo número o una lista separada por comas, por ejemplo 0, 0-1,3 o 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memoria',
                'helper' => 'La cantidad máxima de memoria permitida para este contenedor. Establezca 0 para ilimitado.',
            ],
            'swap' => [
                'label' => 'Intercambio',
                'helper' => 'Establezca 0 para deshabilitar el intercambio o -1 para permitir el intercambio ilimitado.',
            ],
            'disk' => [
                'label' => 'Espacio en disco',
                'helper' => 'Establezca 0 para permitir el uso ilimitado del disco.',
            ],
            'io' => [
                'label' => 'Peso del bloque IO',
                'helper' => 'Avanzado: rendimiento de IO en relación con otros contenedores en ejecución. El valor debe ser de 10 a 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Habilitar OOM Killer',
                'helper' => 'Finaliza el servidor si supera los límites de memoria.',
            ],
            'nest' => [
                'label' => 'Nido',
                'helper' => 'Seleccione el Nest en el que se agrupará este servidor.',
            ],
            'egg' => [
                'label' => 'Huevo',
                'helper' => 'Seleccione el Huevo que definirá cómo debe operar este servidor.',
            ],
            'skip_scripts' => [
                'label' => 'Omitir script de instalación de huevo',
                'helper' => 'Si el Egg seleccionado tiene un script de instalación adjunto, el script se ejecutará durante la instalación a menos que esté marcado.',
            ],
            'image' => [
                'label' => 'Imagen de Docker',
                'helper' => 'Seleccione una imagen del menú desplegable o ingrese una imagen personalizada a continuación.',
            ],
            'custom_image' => [
                'label' => 'Imagen de Docker personalizada',
                'placeholder' => 'O ingresa una imagen personalizada...',
                'helper' => 'Esta es la imagen de Docker predeterminada que se utilizará para ejecutar este servidor.',
            ],
            'startup' => [
                'label' => 'Comando de inicio',
                'helper' => 'Sustitutos disponibles: {{SERVER_MEMORY}}, {{SERVER_IP}} y {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Seleccione un huevo para configurar las variables de servicio',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Modo avanzado',
            'helper' => 'Alternar para mostrar opciones de configuración de servidor adicionales. Actívelo solo si comprende las implicaciones de las configuraciones adicionales.',
        ],
        'external_id' => [
            'label' => 'Identificación externa',
            'helper' => 'Identificador único opcional para este servidor.',
        ],
        'owner' => [
            'label' => 'Dueño',
            'helper' => 'Seleccione el usuario propietario de este servidor.',
        ],
        'name' => [
            'label' => 'Nombre',
            'placeholder' => 'Nombre del servidor',
            'helper' => 'Un nombre corto para este servidor.',
        ],
        'description' => [
            'label' => 'Descripción',
            'placeholder' => 'Descripción del servidor',
            'helper' => 'Descripción opcional para este servidor.',
        ],
        'node' => [
            'label' => 'Nodo',
            'helper' => 'El nodo en el que se implementará este servidor.',
        ],
        'allocation' => [
            'label' => 'Asignación primaria',
            'helper' => 'La asignación de puerto/IP predeterminada para este servidor.',
        ],
        'additional_allocations' => [
            'label' => 'Asignaciones adicionales',
            'helper' => 'Asignaciones adicionales opcionales para asignar.',
        ],
        'nest' => [
            'label' => 'Nido',
            'helper' => 'El nido de servicios para este servidor.',
        ],
        'egg' => [
            'label' => 'Huevo',
            'helper' => 'El huevo que define el comportamiento del servidor.',
        ],
        'startup' => [
            'label' => 'Comando de inicio',
            'helper' => 'El comando de inicio para el servidor.',
        ],
        'image' => [
            'label' => 'Imagen de Docker',
            'helper' => 'Imagen de Docker utilizada para ejecutar este servidor.',
            'custom' => 'Costumbre',
        ],
        'skip_scripts' => [
            'label' => 'Saltar guiones de huevos',
            'helper' => 'Omita los scripts de instalación de huevos durante la creación.',
        ],
        'start_on_completion' => [
            'label' => 'Empezar al finalizar',
            'helper' => 'Inicia automáticamente el servidor después de la instalación.',
        ],
        'memory' => [
            'label' => 'Memoria',
            'helper' => 'Asignación total de memoria. Establezca en 0 para ilimitado. (La memoria ilimitada no funciona para Minecraft Eggs debido al comando de inicio)',
        ],
        'swap' => [
            'label' => 'Intercambio',
            'helper' => 'Intercambiar asignación de memoria. Establezca en 0 para deshabilitar el intercambio o -1 para permitir el intercambio ilimitado.',
        ],
        'disk' => [
            'label' => 'Disco',
            'helper' => 'Asignación de espacio en disco. Establezca en 0 para ilimitado.',
        ],
        'io' => [
            'label' => 'Peso IO',
            'helper' => 'Prioridad relativa de E/S del disco (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Límite de CPU en porcentaje. 100% significa un núcleo completo, 200% significa dos núcleos completos, etc.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Introduzca el tamaño en GiB',
            'helper' => 'Puede ingresar tamaños en GiB usando el sufijo "GiB" (por ejemplo, 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Hilos de CPU',
            'helper' => 'Fijación de hilo opcional. Ejemplo: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Deshabilitar OOM Killer',
            'helper' => 'Evite que el kernel finalice el proceso cuando no tenga memoria.',
        ],
        'database_limit' => [
            'label' => 'Límite de base de datos',
            'helper' => 'Número máximo de bases de datos.',
        ],
        'allocation_limit' => [
            'label' => 'Límite de asignación',
            'helper' => 'Número máximo de asignaciones.',
        ],
        'backup_limit' => [
            'label' => 'Límite de copia de seguridad',
            'helper' => 'Número máximo de copias de seguridad.',
        ],
        'environment' => [
            'key' => 'Variable',
            'value' => 'Valor',
            'helper' => 'Variables ambientales para este huevo.',
        ],
        'use_custom_image' => [
            'label' => 'Usar imagen personalizada',
            'helper' => 'Cambie para usar una imagen de Docker personalizada en lugar de una proporcionada por el huevo.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'owner' => 'Dueño',
        'node' => 'Nodo',
        'allocation' => 'Asignación',
        'status' => 'Estado',
        'egg' => 'Huevo',
        'memory' => 'Memoria',
        'disk' => 'Disco',
        'cpu' => 'CPU',
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'installed' => 'Instalado',
        'no_status' => 'Sin estado',
        'unlimited' => 'Ilimitado',
    ],

    'messages' => [
        'created' => 'El servidor se ha creado correctamente.',
        'updated' => 'El servidor se ha actualizado correctamente.',
        'deleted' => 'El servidor se ha eliminado correctamente.',
    ],

    'actions' => [
        'edit' => 'Editar',
        'random' => 'Aleatorio',
        'toggle_install_status' => 'Alternar estado de instalación',
        'suspend' => 'Suspender',
        'unsuspend' => 'suspender',
        'suspended' => 'Suspendido',
        'unsuspended' => 'no suspendido',
        'reinstall' => 'Reinstalar',
        'delete' => 'Borrar',
        'delete_forcibly' => 'Eliminar a la fuerza',
        'view' => 'Vista',
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
        'server_suspended' => 'El servidor ha sido :action.',
        'server_reinstalled' => 'Este servidor ha sido puesto en cola para una reinstalación comenzando ahora.',
        'server_deleted' => 'El servidor ha sido eliminado exitosamente del sistema.',
        'server_delete_failed' => 'No se pudo eliminar el servidor.',
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
        'primary_allocation_updated' => 'Asignación primaria actualizada.',
        'database_created' => 'Base de datos creada.',
        'database_password_reset' => 'Restablecimiento de contraseña de la base de datos.',
        'database_deleted' => 'Base de datos eliminada.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Información',
            'build_configuration' => 'Configuración de compilación',
            'startup' => 'Puesta en marcha',
            'manage' => 'Administrar',
        ],

        'sections' => [
            'resource_management' => 'Gestión de recursos',
            'application_feature_limits' => 'Límites de las funciones de la aplicación',
            'allocation_management' => 'Gestión de asignaciones',
            'startup_command_modification' => 'Modificación del comando de inicio',
            'service_configuration' => 'Configuración del servicio',
            'docker_image_configuration' => 'Configuración de imagen de Docker',
            'service_variables' => 'Variables de servicio',
            'reinstall_server' => 'Reinstalar el servidor',
            'install_status' => 'Estado de instalación',
            'suspend_server' => 'Suspender servidor',
            'unsuspend_server' => 'Suspender el servidor',
            'transfer_server' => 'Servidor de transferencia',
            'delete_server' => 'Eliminar servidor',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Cambiar estos valores puede provocar una reinstalación. El servidor se detendrá inmediatamente para esa operación.',
            'reinstall_server' => 'Esto reinstalará el servidor con los scripts de servicio asignados. Esto podría sobrescribir los datos del servidor.',
            'install_status' => 'Cambie el estado de instalación de desinstalado a instalado, o viceversa.',
            'suspend_server' => 'Esto detendrá la ejecución de procesos y bloqueará al usuario para que no pueda administrar el servidor a través del panel o API.',
            'unsuspend_server' => 'Esto reactivará el servidor y restaurará el acceso normal de los usuarios.',
            'transfer_server_transferring' => 'Este servidor se está transfiriendo actualmente a otro nodo.',
            'transfer_server' => 'Transfiera este servidor a otro nodo conectado a este panel.',
            'delete_server' => 'Esto elimina permanentemente el servidor del panel y del Agente. Forzar eliminación omite la eliminación del agente si es necesario.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Nombre del servidor',
                'helper' => 'Límites de caracteres: a-zA-Z0-9_-, espacios y caracteres imprimibles estándar.',
            ],
            'server_owner' => [
                'label' => 'Propietario del servidor',
                'helper' => 'El cambio de propiedad revoca automáticamente los tokens de demonio del propietario anterior.',
            ],
            'server_description' => [
                'label' => 'Descripción del servidor',
                'helper' => 'Una breve descripción de este servidor.',
            ],
            'server_uuid' => [
                'label' => 'UUID del servidor',
            ],
            'server_uuid_short' => [
                'label' => 'UUID del servidor (breve)',
            ],
            'external_identifier' => [
                'label' => 'Identificador externo',
                'helper' => 'Déjelo vacío para no asignar un identificador externo. La ID externa debe ser exclusiva de este servidor.',
            ],
            'game_port' => [
                'label' => 'Puerto de juego',
                'helper' => 'La dirección de conexión predeterminada que se utilizará para este servidor de juegos.',
            ],
            'additional_ports' => [
                'label' => 'Puertos adicionales',
                'helper' => 'Asigne o elimine puertos adicionales. No se pueden asignar puertos idénticos en diferentes IP al mismo servidor.',
            ],
            'startup_command' => [
                'label' => 'Comando de inicio',
                'helper' => 'Disponible de forma predeterminada: {{SERVER_MEMORY}}, {{SERVER_IP}} y {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Comando de inicio predeterminado',
                'error' => 'ERROR: ¡Inicio no definido!',
            ],
            'cpu_limit' => [
                'label' => 'Límite de CPU',
                'helper' => 'Cada núcleo virtual es del 100%. Establezca 0 para tiempo de CPU sin restricciones.',
            ],
            'cpu_pinning' => [
                'label' => 'Fijación de CPU',
                'helper' => 'Avanzado: dejar en blanco para todos los núcleos. Ejemplos: 0, 0-1,3 o 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Memoria asignada',
                'helper' => 'La cantidad máxima de memoria permitida para este contenedor. Establezca 0 para ilimitado.',
            ],
            'allocated_swap' => [
                'label' => 'Swap asignado',
                'helper' => 'Establezca 0 para deshabilitar el intercambio o -1 para permitir el intercambio ilimitado.',
            ],
            'disk_space_limit' => [
                'label' => 'Límite de espacio en disco',
                'helper' => 'Establezca 0 para permitir el uso ilimitado del disco.',
            ],
            'block_io_proportion' => [
                'label' => 'Proporción de E/S del bloque',
                'helper' => 'Avanzado: rendimiento de IO en relación con otros contenedores en ejecución. El valor debe ser de 10 a 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Deshabilitar OOM Killer',
                'helper' => 'Habilitar OOM Killer puede provocar que los procesos del servidor se cierren inesperadamente.',
            ],
            'database_limit' => [
                'label' => 'Límite de base de datos',
                'helper' => 'El número total de bases de datos que un usuario puede crear para este servidor.',
            ],
            'allocation_limit' => [
                'label' => 'Límite de asignación',
                'helper' => 'El número total de asignaciones que un usuario puede crear para este servidor.',
            ],
            'backup_limit' => [
                'label' => 'Límite de copia de seguridad',
                'helper' => 'El número total de copias de seguridad que se pueden crear para este servidor.',
            ],
            'image' => [
                'label' => 'Imagen',
                'helper' => 'Seleccione una imagen del menú desplegable o ingrese una imagen personalizada a continuación.',
            ],
            'custom_image' => [
                'label' => 'Imagen personalizada',
                'placeholder' => 'O ingresa una imagen personalizada...',
                'helper' => 'Esta es la imagen de Docker que se utilizará para ejecutar este servidor.',
            ],
            'transfer_node' => [
                'label' => 'Nodo',
                'helper' => 'El nodo al que se transferirá este servidor.',
            ],
            'transfer_allocation' => [
                'label' => 'Asignación predeterminada',
                'helper' => 'La asignación principal que se asignará a este servidor.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Asignaciones adicionales',
                'helper' => 'Asignaciones adicionales para asignar a este servidor en el momento de la transferencia.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstalar el servidor',
            'toggle_install_status' => 'Alternar estado de instalación',
            'suspend_server' => 'Suspender servidor',
            'unsuspend_server' => 'Suspender el servidor',
            'transfer_server' => 'Servidor de transferencia',
            'confirm' => 'Confirmar',
            'delete_server' => 'Eliminar servidor',
            'forcibly_delete_server' => 'Eliminar servidor a la fuerza',
        ],
    ],

    'allocations' => [
        'title' => 'Asignaciones',

        'table' => [
            'ip' => 'IP',
            'port' => 'Puerto',
            'alias' => 'Alias',
            'primary' => 'Primario',
            'notes' => 'Notas',
            'created' => 'Creado',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Ningún alias asignado',
        ],

        'actions' => [
            'make_primary' => 'Hacer primario',
        ],
    ],

    'databases' => [
        'title' => 'Bases de datos',

        'table' => [
            'database' => 'Base de datos',
            'username' => 'Nombre de usuario',
            'remote' => 'Remoto',
            'host' => 'Anfitrión',
            'max_connections' => 'Conexiones máximas',
            'created' => 'Creado',
        ],

        'placeholder' => [
            'unlimited' => 'Ilimitado',
        ],

        'actions' => [
            'create_database' => 'Crear base de datos',
            'reset_password' => 'Restablecer contraseña',
            'delete' => 'Borrar',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Nombre de la base de datos',
                'helper' => 'El panel pondrá como prefijo el ID del servidor, que coincidirá con el antiguo panel de administración.',
            ],
            'database_host' => [
                'label' => 'Host de base de datos',
            ],
            'remote' => [
                'label' => 'Remoto',
            ],
            'max_connections' => [
                'label' => 'Conexiones máximas',
            ],
        ],
    ],
];
