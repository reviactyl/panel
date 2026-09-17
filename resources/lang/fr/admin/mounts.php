<?php

return [

    'label' => 'Monter',
    'plural_label' => 'Montures',

    'sections' => [
        'configuration' => 'Configuration du montage',
    ],

    'fields' => [
        'name' => 'Nom',
        'description' => 'Description',
        'source' => 'Chemin Source',
        'target' => 'Chemin Cible',
        'read_only' => 'Lecture seule',
        'user_mountable' => 'Montable par l\'utilisateur',
    ],

    'helpers' => [
        'name' => 'Un nom unique utilisé pour séparer cette monture d\'une autre.',
        'description' => 'Une description plus longue et lisible par l\'homme de cette monture.',
        'source' => 'Le chemin du fichier sur la machine hôte à monter sur les conteneurs.',
        'target' => 'Le chemin à l’intérieur du conteneur sous lequel monter ceci.',
        'read_only' => 'S\'il est défini, le montage sera en lecture seule à l\'intérieur du conteneur.',
        'user_mountable' => 'S\'il est défini, les utilisateurs pourront le monter sur leurs serveurs.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nom',
        'source' => 'Source',
        'target' => 'Cible',
        'read_only' => 'Lecture seule',
        'user_mountable' => 'Montable par l\'utilisateur',
    ],

    'actions' => [
        'attach_egg' => 'Attacher l\'œuf',
        'attach_node' => 'Attacher un nœud',
    ],

];
