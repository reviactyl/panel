<?php

return [

    'label' => 'Plats',
    'plural-label' => 'Platser',

    'section' => [
        'title' => 'Platsdetaljer',
        'description' => 'Definiera en plats som noder kan tilldelas till.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Kortkod',
            'placeholder' => 'us.nyc.1',
            'helper' => 'En kort identifierare för denna plats.',
        ],

        'long' => [
            'label' => 'Beskrivning',
            'placeholder' => 'New York City, NY, USA',
            'helper' => 'En längre beskrivning av denna plats.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Kortkod',
        'long' => 'Beskrivning',
        'nodes' => 'Noder',
        'servers' => 'Servrar',
        'created' => 'Skapad',
    ],

    'actions' => [
        'edit' => 'Redigera',
        'delete' => 'Ta bort',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Kan inte ta bort en plats som har kopplade noder.',
    ],

];
