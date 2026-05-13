<?php

return [

    'label' => 'Base de données',
    'plural-label' => 'Bases de données',

    'none' => 'Aucun',

    'sections' => [
        'host_details' => [
            'title' => 'Détails Serveur',
            'description' => 'Configurer les paramètres de connexion au serveur de base de données.',
        ],

        'authentication' => [
            'title' => 'Authentification',
        ],

        'linked_node' => [
            'title' => 'Nœud lié',
        ],
    ],

    'placeholders' => [
        'name' => 'MySQL de production',
        'host' => '127.0.0.1',
        'username' => 'reviactyl',
    ],

    'helpers' => [
        'host' => 'Le nom d’hôte ou l’adresse IP du serveur de base de données.',
        'linked_node' => 'Optionnel. Associer cet hôte à un nœud spécifique.',
    ],

    'fields' => [
        'linked_node' => 'Nœud lié',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nom',
        'host' => 'Hôte',
        'port' => 'Port',
        'username' => 'Nom d\'utilisateur',
        'linked_node' => 'Nœud lié',
        'databases' => 'Bases de données',
        'created' => 'Créé',
    ],

    'actions' => [
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ],

    'errors' => [
        'cannot_delete' => 'Vous ne pouvez pas supprimer cet hôte de base de données tant que des bases de données y sont associées.',
    ],

];
