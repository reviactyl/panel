<?php

return [

    'label' => 'Standort',
    'plural-label' => 'Standorte',

    'section' => [
        'title' => 'Standort Details',
        'description' => 'Definiere einen Standort zu dem Nodes hinzugefügt werden können.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Kurzkennung',
            'helper' => 'Ein Kurze Kennung für diesen Standort.',
        ],

        'long' => [
            'label' => 'Beschreibung',
            'helper' => 'Eine längere Beschreibung dieses Standortes.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Kurzkennung',
        'long' => 'Beschreibung',
        'nodes' => 'Knoten',
        'servers' => 'Server',
        'created' => 'Erstellt',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Sie können kein Standort mit Nodes löschen.',
    ],

];
