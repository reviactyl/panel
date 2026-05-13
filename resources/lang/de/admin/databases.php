<?php

return [

    'label' => 'Datenbank',
    'plural-label' => 'Datenbanken',

    'none' => 'Keine',

    'sections' => [
        'host_details' => [
            'title' => 'Gastgeberdetails',
            'description' => 'Datenbank in den Host Einstellungen konfigurieren.',
        ],

        'authentication' => [
            'title' => 'Authentifikation',
        ],

        'linked_node' => [
            'title' => 'Verknüpfte Node',
        ],
    ],

    'placeholders' => [
        'name' => 'Produktions MySQL',
        'host' => '127.0.0.1',
        'username' => 'Reviactyl',
    ],

    'helpers' => [
        'host' => 'Der Hostname oder die IP-Adresse des Datenbankservers.',
        'linked_node' => 'Optional. Verknüpft diesen Host mit einer Node.',
    ],

    'fields' => [
        'linked_node' => 'Verknüpfte Node',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'host' => 'Gastgeber',
        'port' => 'Hafen',
        'username' => 'Benutzername',
        'linked_node' => 'Verknüpfte Node',
        'databases' => 'Datenbanken',
        'created' => 'Erstellt',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
    ],

    'errors' => [
        'cannot_delete' => 'Sie können keinen Datenbank Host mit erstellten Datenbanken löschen.',
    ],

];
