<?php

return [

    'label' => 'Database',
    'plural-label' => 'Databases',

    'none' => 'None',

    'sections' => [
        'host_details' => [
            'title' => 'Host Details',
            'description' => 'Configure the database host connection settings.',
        ],

        'authentication' => [
            'title' => 'Authentication',
        ],

        'linked_node' => [
            'title' => 'Linked Node',
        ],
    ],

    'placeholders' => [
        'name' => 'Production MySQL',
        'host' => '127.0.0.1',
        'username' => 'reviactyl',
    ],

    'helpers' => [
        'host' => 'The hostname or IP address of the database server.',
        'linked_node' => 'Optional. Link this host to a specific node.',
    ],

    'fields' => [
        'linked_node' => 'Linked Node',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'host' => 'Host',
        'port' => 'Port',
        'username' => 'Username',
        'linked_node' => 'Linked Node',
        'databases' => 'Databases',
        'created' => 'Created',
    ],

    'actions' => [
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],

    'errors' => [
        'cannot_delete' => 'Cannot delete a database host with associated databases.',
    ],

];
