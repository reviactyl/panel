<?php

return [
    'label' => 'Rôle',
    'plural-label' => 'Rôles',
    'all' => 'Tout',
    'select_all' => 'Tout sélectionner',
    'deselect_all' => 'Tout désélectionner',
    'details' => [
        'title' => 'Détails du rôle',
        'description' => 'Les rôles déterminent ce qu’un utilisateur peut faire dans le panel, dans la zone d’administration et sur les serveurs qui ne lui appartiennent pas.',
        'name' => 'Nom',
        'name_placeholder' => 'Équipe de support',
        'description_label' => 'Description',
        'description_placeholder' => 'À quoi sert ce rôle.',
    ],
    'table' => [
        'name' => 'Nom',
        'permissions' => 'Permissions',
        'users' => 'Utilisateurs',
        'updated' => 'Dernière mise à jour',
    ],
    'groups' => [
        'management' => [
            'title' => 'Gestion',
            'description' => 'Les serveurs, les personnes qui les utilisent et l’infrastructure sur laquelle ils tournent.',
        ],
        'service' => [
            'title' => 'Gestion des services',
            'description' => 'Ce qui peut être installé sur un serveur et la façon dont le panel lui-même est étendu et mis à jour.',
        ],
        'administration' => [
            'title' => 'Administration',
            'description' => 'Configuration, apparence et suivi à l’échelle du panel.',
        ],
    ],
    'resources' => [
        'servers' => 'Serveurs',
        'users' => 'Utilisateurs',
        'roles' => 'Rôles',
        'nodes' => 'Noeuds',
        'locations' => 'Emplacements',
        'database_hosts' => 'Hôtes de bases de données',
        'nests' => 'Nids',
        'eggs' => 'Eggs',
        'mounts' => 'Supports',
        'alerts' => 'Alertes',
        'webhooks' => 'Webhooks',
        'extensions' => 'Rallonges',
        'activity_log' => 'Journal d’activité',
        'monitoring' => 'Surveillance',
        'settings' => 'Paramètres',
        'designify' => 'Designify',
        'updates' => 'Mises à jour logicielles',
    ],
    'actions' => [
        'send_email' => 'Envoyer un e-mail',
        'read' => 'Voir',
        'create' => 'Créer',
        'update' => 'Modifier',
        'delete' => 'Supprimer',
        'assign' => 'Attribuer aux utilisateurs',
        'manage' => 'Gérer',
        'install' => 'Installer les mises à jour',
    ],
    'messages' => [
        'in_use' => 'Ce rôle est encore attribué à des utilisateurs. Retirez-le avant de le supprimer.',
    ],
    'user' => [
        'roles' => 'Rôles',
        'roles_self' => 'Vous ne pouvez pas modifier vos propres rôles.',
        'none' => 'Aucun',
    ],
];
