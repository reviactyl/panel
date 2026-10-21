<?php

return [
    'label' => 'Rolle',
    'plural-label' => 'Rollen',
    'all' => 'Alle',
    'select_all' => 'Alle auswählen',
    'deselect_all' => 'Alle abwählen',
    'details' => [
        'title' => 'Rollendetails',
        'description' => 'Rollen legen fest, was ein Benutzer im Panel tun kann, im Adminbereich und auf Servern, die ihm nicht gehören.',
        'name' => 'Name',
        'name_placeholder' => 'Support-Team',
        'description_label' => 'Beschreibung',
        'description_placeholder' => 'Wofür diese Rolle gedacht ist.',
    ],
    'table' => [
        'name' => 'Name',
        'permissions' => 'Berechtigungen',
        'users' => 'Benutzer',
        'updated' => 'Zuletzt aktualisiert',
    ],
    'groups' => [
        'management' => [
            'title' => 'Verwaltung',
            'description' => 'Server, die Personen, die sie nutzen, und die Infrastruktur, auf der sie laufen.',
        ],
        'service' => [
            'title' => 'Dienstverwaltung',
            'description' => 'Was auf einem Server installiert werden kann und wie das Panel selbst erweitert und aktualisiert wird.',
        ],
        'administration' => [
            'title' => 'Verwaltung',
            'description' => 'Panelweite Konfiguration, Darstellung und Einblicke.',
        ],
    ],
    'resources' => [
        'servers' => 'Server',
        'users' => 'Benutzer',
        'roles' => 'Rollen',
        'nodes' => 'Knoten',
        'locations' => 'Standorte',
        'database_hosts' => 'Datenbank-Hosts',
        'nests' => 'Nester',
        'eggs' => 'Eggs',
        'mounts' => 'Reittiere',
        'alerts' => 'Hinweise',
        'webhooks' => 'Webhooks',
        'extensions' => 'Erweiterungen',
        'activity_log' => 'Aktivitäts-Log',
        'monitoring' => 'Monitoring',
        'settings' => 'Einstellungen',
        'designify' => 'Designify',
        'updates' => 'Software-Updates',
    ],
    'actions' => [
        'send_email' => 'E-Mail senden',
        'read' => 'Ansehen',
        'create' => 'Erstellen',
        'update' => 'Bearbeiten',
        'delete' => 'Löschen',
        'assign' => 'Benutzern zuweisen',
        'manage' => 'Verwalten',
        'install' => 'Updates installieren',
    ],
    'messages' => [
        'in_use' => 'Diese Rolle ist noch Benutzern zugewiesen. Entferne sie dort, bevor du sie löschst.',
    ],
    'user' => [
        'roles' => 'Rollen',
        'roles_self' => 'Du kannst deine eigenen Rollen nicht ändern.',
        'none' => 'Keine',
    ],
];
