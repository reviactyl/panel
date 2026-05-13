<?php

return [

    'label' => 'Base de datos',
    'plural-label' => 'Bases de datos',

    'none' => 'Ninguno',

    'sections' => [
        'host_details' => [
            'title' => 'Detalles del anfitrión',
            'description' => 'Configure los ajustes de conexión del host de la base de datos.',
        ],

        'authentication' => [
            'title' => 'Autenticación',
        ],

        'linked_node' => [
            'title' => 'Nodo vinculado',
        ],
    ],

    'placeholders' => [
        'name' => 'MySQL de producción',
        'host' => '127.0.0.1',
        'username' => 'reviactilo',
    ],

    'helpers' => [
        'host' => 'El nombre de host o la dirección IP del servidor de la base de datos.',
        'linked_node' => 'Opcional. Vincula este host a un nodo específico.',
    ],

    'fields' => [
        'linked_node' => 'Nodo vinculado',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'host' => 'Anfitrión',
        'port' => 'Puerto',
        'username' => 'Nombre de usuario',
        'linked_node' => 'Nodo vinculado',
        'databases' => 'Bases de datos',
        'created' => 'Creado',
    ],

    'actions' => [
        'edit' => 'Editar',
        'delete' => 'Borrar',
    ],

    'errors' => [
        'cannot_delete' => 'No se puede eliminar un host de base de datos con bases de datos asociadas.',
    ],

];
