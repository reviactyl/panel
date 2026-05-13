<?php

return [

    'label' => 'Emplacements',
    'plural-label' => 'Emplacements',

    'section' => [
        'title' => 'Détails des emplacements',
        'description' => 'Définir un emplacement auquel des nœuds peuvent être assignés.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Code Court',
            'placeholder' => 'nous.nyc.1',
            'helper' => 'Un identifiant court pour cet emplacement.',
        ],

        'long' => [
            'label' => 'Description',
            'placeholder' => 'New York, NY, États-Unis',
            'helper' => 'Une description plus détaillée de cet emplacement.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Code Court',
        'long' => 'Description',
        'nodes' => 'Noeuds',
        'servers' => 'Serveurs',
        'created' => 'Créé',
    ],

    'actions' => [
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Impossible de supprimer un emplacement ayant des nœuds associés.',
    ],

];
