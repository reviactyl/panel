<?php

return [

    'label' => 'Location',
    'plural-label' => 'Locations',

    'section' => [
        'title' => 'Location Details',
        'description' => 'Define a location that nodes can be assigned to.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Short Code',
            'placeholder' => 'us.nyc.1',
            'helper' => 'A short identifier for this location.',
        ],

        'long' => [
            'label' => 'Description',
            'placeholder' => 'New York City, NY, USA',
            'helper' => 'A longer description of this location.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Short Code',
        'long' => 'Description',
        'nodes' => 'Nodes',
        'servers' => 'Servers',
        'created' => 'Created',
    ],

    'actions' => [
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Cannot delete a location with associated nodes.',
    ],

];
