<?php

return [

    'label' => 'Local',
    'plural-label' => 'Locais',

    'section' => [
        'title' => 'Detalhes do Local',
        'description' => 'Defina um local ao qual os nodes podem ser atribuídos.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Código curto',
            'helper' => 'Um identificador curto para este local.',
        ],

        'long' => [
            'label' => 'Descrição',
            'helper' => 'Uma descrição mais detalhada deste local',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Código curto',
        'long' => 'Descrição',
        'nodes' => 'Nós',
        'servers' => 'Servidores',
        'created' => 'Criar',
    ],

    'actions' => [
        'edit' => 'Editar',
        'delete' => 'Apagar',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Não é possível excluir um local com nodes associados.',
    ],

];
