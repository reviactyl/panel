<?php

return [

    'label' => 'Montieren',
    'plural_label' => 'Reittiere',

    'sections' => [
        'configuration' => 'Mount Konfiguration',
    ],

    'fields' => [
        'name' => 'Name',
        'description' => 'Beschreibung',
        'source' => 'Quell-Pfad',
        'target' => 'Ziel-Pfad',
        'read_only' => 'Nur Lesen',
        'user_mountable' => 'Benutzer Mountbar',
    ],

    'helpers' => [
        'name' => 'Ein eindeutiger Name muss verwendet werden, um diesen Mount von anderen zu halten.',
        'description' => 'Eine längere, Menschen lesbare Beschreibung dieses Mounts.',
        'source' => 'Der Datei-Pfad auf der Host-Maschine zu den Containern.',
        'target' => 'Der Pfad auf dem Container.',
        'read_only' => 'Falls gesetzt, wird dieser Mount nur lesbar im Container sein.',
        'user_mountable' => 'Falls gesetzt, wird dieser Mount von Benutzern verwendet werden können.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'source' => 'Quelle',
        'target' => 'Ziel',
        'read_only' => 'Nur Lesbar',
        'user_mountable' => 'Benutzer Mountbar',
    ],

    'actions' => [
        'attach_egg' => 'Ei anbringen',
        'attach_node' => 'Knoten anhängen',
    ],

];
