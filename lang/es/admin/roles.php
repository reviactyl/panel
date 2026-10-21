<?php

return [
    'label' => 'Rol',
    'plural-label' => 'Roles',
    'all' => 'Todos',
    'select_all' => 'Seleccionar todo',
    'deselect_all' => 'Deseleccionar todo',
    'details' => [
        'title' => 'Detalles del rol',
        'description' => 'Los roles deciden qué puede hacer un usuario en el panel, en el área de administración y en servidores que no son suyos.',
        'name' => 'Nombre',
        'name_placeholder' => 'Equipo de soporte',
        'description_label' => 'Descripción',
        'description_placeholder' => 'Para qué sirve este rol.',
    ],
    'table' => [
        'name' => 'Nombre',
        'permissions' => 'Permisos',
        'users' => 'Usuarios',
        'updated' => 'Última actualización',
    ],
    'groups' => [
        'management' => [
            'title' => 'Gestión',
            'description' => 'Los servidores, las personas que los usan y la infraestructura en la que funcionan.',
        ],
        'service' => [
            'title' => 'Gestión de servicios',
            'description' => 'Qué se puede instalar en un servidor y cómo se amplía y actualiza el propio panel.',
        ],
        'administration' => [
            'title' => 'Administración',
            'description' => 'Configuración, apariencia e información de todo el panel.',
        ],
    ],
    'resources' => [
        'servers' => 'Servidores',
        'users' => 'Usuarios',
        'roles' => 'Roles',
        'nodes' => 'Nodos',
        'locations' => 'Ubicaciones',
        'database_hosts' => 'Hosts de bases de datos',
        'nests' => 'Nidos',
        'eggs' => 'Eggs',
        'mounts' => 'Monturas',
        'alerts' => 'Alertas',
        'webhooks' => 'Webhooks',
        'extensions' => 'Extensiones',
        'activity_log' => 'Registro de actividad',
        'monitoring' => 'Monitorización',
        'settings' => 'Ajustes',
        'designify' => 'Designify',
        'updates' => 'Actualizaciones de software',
    ],
    'actions' => [
        'send_email' => 'Enviar correo',
        'read' => 'Ver',
        'create' => 'Crear',
        'update' => 'Editar',
        'delete' => 'Eliminar',
        'assign' => 'Asignar a usuarios',
        'manage' => 'Gestionar',
        'install' => 'Instalar actualizaciones',
    ],
    'messages' => [
        'in_use' => 'Este rol sigue asignado a usuarios. Quítaselo antes de eliminarlo.',
    ],
    'user' => [
        'roles' => 'Roles',
        'roles_self' => 'No puedes cambiar tus propios roles.',
        'none' => 'Ninguno',
    ],
];
