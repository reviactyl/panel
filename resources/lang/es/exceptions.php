<?php

return [
    'daemon_connection_failed' => 'Hubo una excepción al intentar comunicarse con el daemon resultando en un código de respuesta HTTP/:code. Esta excepción ha sido registrada.',
    'node' => [
        'servers_attached' => 'Un nodo no debe tener servidores vinculados para poder ser eliminado.',
        'daemon_off_config_updated' => 'La configuración del demonio se actualizó, sin embargo, se encontró un error al intentar actualizar automáticamente el archivo de configuración en el demonio. Deberá actualizar manualmente el archivo de configuración (config.yml) para que el demonio aplique estos cambios.',
    ],
    'allocations' => [
        'server_using' => 'Un servidor está actualmente asignado a esta asignación. Una asignación solo puede ser eliminada si no hay ningún servidor asignado actualmente.',
        'too_many_ports' => 'No se admite agregar más de 1000 puertos en un solo rango a la vez.',
        'invalid_mapping' => 'El mapeo proporcionado para :port era inválido y no pudo ser procesado.',
        'cidr_out_of_range' => 'La notación CIDR solo permite máscaras entre /25 y /32.',
        'port_out_of_range' => 'Los puertos en una asignación deben ser mayores que 1024 y menores o iguales a 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Un Nido con servidores activos adjuntos no puede ser eliminado del Panel.',
        'egg' => [
            'delete_has_servers' => 'Un Egg con servidores activos adjuntos no puede ser eliminado del Panel.',
            'invalid_copy_id' => 'El Egg seleccionado para copiar un script no existe, o está copiando un script en sí mismo.',
            'must_be_child' => 'La directiva "Copiar Configuración De" para este Egg debe ser una opción hija del Nido seleccionado.',
            'has_children' => 'Este Egg es padre de uno o más Eggs. Por favor elimina esos Eggs antes de eliminar este Egg.',
        ],
        'variables' => [
            'env_not_unique' => 'La variable de entorno :name debe ser única para este Egg.',
            'reserved_name' => 'La variable de entorno :name está protegida y no puede ser asignada a una variable.',
            'bad_validation_rule' => 'La regla de validación ":rule" no es una regla válida para esta aplicación.',
        ],
        'importer' => [
            'json_error' => 'Hubo un error al intentar analizar el archivo JSON: :error.',
            'file_error' => 'El archivo JSON proporcionado no era válido.',
            'invalid_json_provided' => 'El archivo JSON proporcionado no está en un formato que pueda ser reconocido.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'No está permitido editar tu propia cuenta de subusuario.',
        'user_is_owner' => 'No puedes añadir al propietario del servidor como subusuario para este servidor.',
        'subuser_exists' => 'Un usuario con esa dirección de correo ya está asignado como subusuario para este servidor.',
    ],
    'subuser_preview' => [
        'start_blocked' => 'No puedes iniciar otra vista previa mientras el modo de vista previa esté activo.',
        'owner_only' => 'Solo el propietario del servidor puede ver una vista previa de un subusuario.',
        'session_unavailable' => 'Esta sesión de vista previa ya no está disponible.',
        'session_expired' => 'Esta sesión de vista previa ha caducado.',
        'concurrent_start' => 'Ya se ha iniciado una sesión de vista previa.',
        'account_unavailable' => 'La información de la cuenta no está disponible durante la vista previa del subusuario.',
        'categories_unavailable' => 'Las categorías de servidores personales no están disponibles durante la fase de prueba de los subusuarios.',
        'permission_denied' => 'No tienes permiso para realizar esta acción en la vista previa.',
        'resource_unavailable' => 'Este recurso no está disponible durante la fase de vista previa para subusuarios.',
        'live_connection_unavailable' => 'Esta conexión en directo no está disponible durante la vista previa del subusuario.',
        'file_not_found' => 'El archivo solicitado no existe en esta vista previa.',
        'file_too_large' => 'Este archivo es demasiado grande para mostrarlo en la vista previa.',
        'state_too_large' => 'Esta vista previa ha alcanzado su límite de almacenamiento.',
        'unsafe_pull_url' => 'Solo se pueden incluir en la vista previa las URL HTTPS seguras.',
        'action_unavailable' => 'Esta acción no está disponible durante la vista previa del subusuario.',
        'database_limit' => 'Este servidor ha alcanzado el límite de su base de datos.',
        'database_host_unavailable' => 'No hay ningún servidor de base de datos disponible para este servidor.',
        'task_limit' => 'Esta programación ha alcanzado su límite de tareas.',
        'allocation_limit' => 'Este servidor ha alcanzado su límite de asignación.',
        'allocation_unavailable' => 'No hay asignación adicional disponible para este servidor.',
        'primary_allocation' => 'La asignación principal no se puede eliminar.',
        'backup_limit' => 'Este servidor ha alcanzado su límite de copias de seguridad.',
        'locked_backup' => 'No se puede eliminar una copia de seguridad bloqueada.',
        'variable_unavailable' => 'La variable de entorno no está disponible o es de solo lectura.',
        'docker_image_unavailable' => 'La imagen de Docker seleccionada no está disponible para este servidor.',
    ],
    'databases' => [
        'delete_has_databases' => 'No se puede eliminar un servidor host de base de datos que tiene bases de datos activas vinculadas.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'El tiempo máximo de intervalo para una tarea encadenada es de 15 minutos.',
    ],
    'locations' => [
        'has_nodes' => 'No se puede eliminar una ubicación que tiene nodos activos adjuntos.',
    ],
    'users' => [
        'node_revocation_failed' => 'Error al revocar claves en <a href=":link">Nodo #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'No se encontraron nodos que satisfagan los requisitos especificados para el despliegue automático.',
        'no_viable_allocations' => 'No se encontraron asignaciones que satisfagan los requisitos para el despliegue automático.',
    ],
    'api' => [
        'resource_not_found' => 'El recurso solicitado no existe en este servidor.',
    ],
    'social' => [
        'unlink_only_login' => 'No puede desvincular su único método de inicio de sesión sin establecer primero una contraseña.',
    ],
];
