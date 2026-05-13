<?php

return [

    'label' => 'Ubicación',
    'plural-label' => 'Ubicaciones',

    'section' => [
        'title' => 'Detalles de la ubicación',
        'description' => 'Defina una ubicación a la que se puedan asignar los nodos.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Código corto',
            'placeholder' => 'nosotros.nyc.1',
            'helper' => 'Un identificador corto para esta ubicación.',
        ],

        'long' => [
            'label' => 'Descripción',
            'placeholder' => 'Nueva York, Nueva York, Estados Unidos',
            'helper' => 'Una descripción más larga de esta ubicación.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Código corto',
        'long' => 'Descripción',
        'nodes' => 'Nodos',
        'servers' => 'Servidores',
        'created' => 'Creado',
    ],

    'actions' => [
        'edit' => 'Editar',
        'delete' => 'Borrar',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'No se puede eliminar una ubicación con nodos asociados.',
    ],

];
