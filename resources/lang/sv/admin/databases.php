<?php

return [

    'label' => 'Databas',
    'plural-label' => 'Databaser',

    'none' => 'Ingen',

    'sections' => [
        'host_details' => [
            'title' => 'Värddetaljer',
            'description' => 'Konfigurera anslutningsinställningar för databasservrar.',
        ],

        'authentication' => [
            'title' => 'Autentisering',
        ],

        'linked_node' => [
            'title' => 'Länkad nod',
        ],
    ],

    'placeholders' => [
        'name' => 'Produktions-MySQL',
        'host' => '127.0.0.1',
        'username' => 'reviactyl',
    ],

    'helpers' => [
        'host' => 'Värdnamn eller IP-adress till databasservern.',
        'linked_node' => 'Valfritt. Länka denna värd till en specifik nod.',
    ],

    'fields' => [
        'linked_node' => 'Länka denna nod',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Namn',
        'host' => 'Host',
        'port' => 'Port',
        'username' => 'Användarnamn',
        'linked_node' => 'Länka noder',
        'databases' => 'Databaser',
        'created' => 'Skapad',
    ],

    'actions' => [
        'edit' => 'Redigera',
        'delete' => 'Ta bort',
    ],

    'errors' => [
        'cannot_delete' => 'Kan inte ta bort en databasservärd som har kopplade databaser.',
    ],

];
