<?php

return [

    'tabs' => [
        'configuration' => 'Configuración del huevo',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Configuración',
        ],
        'identity' => [
            'title' => 'Identidad',
        ],
        'docker_images' => [
            'title' => 'Imágenes de Docker',
            'description' => 'Las imágenes de la ventana acoplable disponibles para los servidores que usan este huevo. Ingrese uno por línea.',
        ],
        'process_management' => [
            'title' => 'Gestión de procesos',
        ],
        'variables' => [
            'title' => 'variables',
        ],
        'install_script' => [
            'title' => 'Instalar secuencia de comandos',
        ],
    ],

    'fields' => [
        'nest' => 'Nido',
        'uuid' => 'UUID',
        'name' => 'Nombre',
        'author' => 'Autor',
        'image' => 'Imagen',
        'description' => 'Descripción',
        'image_name' => 'Nombre de la imagen',
        'image_uri' => 'URI de imagen',
        'add_docker_image' => 'Agregar imagen de Docker',
        'force_outgoing_ip' => 'Forzar IP saliente',
        'features' => 'Características',
        'startup' => 'Comando de inicio',
        'config_stop' => 'Comando de parada',
        'config_from' => 'Copiar configuración de',
        'config_startup' => 'Iniciar configuración (JSON)',
        'config_logs' => 'Configuración de registro (JSON)',
        'config_files' => 'Archivos de configuración (JSON)',
        'file_denylist' => 'Lista de archivos rechazados',
        'env_variable' => 'Variable de entorno',
        'user_viewable' => 'Los usuarios pueden ver',
        'user_editable' => 'Los usuarios pueden editar',
        'rules' => 'Reglas de entrada',
        'default_value' => 'Valor predeterminado',
        'script_install' => 'Instalar secuencia de comandos',
        'script_container' => 'Contenedor de secuencias de comandos',
        'script_entry' => 'Comando de punto de entrada del script',
        'copy_script_from' => 'Copiar guión de',
        'script_is_privileged' => 'Privilegiado',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Obliga a todo el tráfico de red saliente a tener su IP de origen NATizada a la IP de la IP de asignación principal del servidor.',
        'features' => 'Características adicionales pertenecientes al huevo. Útil para configurar modificaciones adicionales del panel.',
        'file_denylist' => 'Archivos que no deben ser editados por el usuario.',
        'script_is_privileged' => 'Ejecute el script de instalación como contenedor privilegiado (raíz).',
    ],

    'actions' => [
        'export' => 'Exportar',
        'create' => 'crear huevo',
        'edit' => 'Editar',
    ],

    'notices' => [
        'cannot_delete' => 'No se puede eliminar el huevo',
        'cannot_delete_body' => 'Este huevo tiene servidores :count asociados. Elimínelos o reasígnelos primero.',
        'cannot_delete_multiple' => 'No se pueden eliminar huevos con servidores',
        'cannot_delete_multiple_body' => 'Los huevos :count tienen servidores asociados y se omitieron.',
    ],

];
