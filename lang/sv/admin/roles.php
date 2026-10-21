<?php

return [
    'label' => 'Roll',
    'plural-label' => 'Roller',
    'all' => 'Alla',
    'select_all' => 'Markera alla',
    'deselect_all' => 'Avmarkera alla',
    'details' => [
        'title' => 'Rolldetaljer',
        'description' => 'Roller avgör vad en användare kan göra i panelen, i adminområdet och på servrar som inte är deras egna.',
        'name' => 'Namn',
        'name_placeholder' => 'Supportteam',
        'description_label' => 'Beskrivning',
        'description_placeholder' => 'Vad den här rollen är till för.',
    ],
    'table' => [
        'name' => 'Namn',
        'permissions' => 'Behörigheter',
        'users' => 'Användare',
        'updated' => 'Senast uppdaterad',
    ],
    'groups' => [
        'management' => [
            'title' => 'Hantering',
            'description' => 'Servrar, personerna som använder dem och infrastrukturen de körs på.',
        ],
        'service' => [
            'title' => 'Servicehantering',
            'description' => 'Vad som kan installeras på en server och hur själva panelen utökas och uppdateras.',
        ],
        'administration' => [
            'title' => 'Administration',
            'description' => 'Panelens konfiguration, utseende och insikter.',
        ],
    ],
    'resources' => [
        'servers' => 'Servrar',
        'users' => 'Användare',
        'roles' => 'Roller',
        'nodes' => 'Noder',
        'locations' => 'Platser',
        'database_hosts' => 'Databasvärdar',
        'nests' => 'Burar',
        'eggs' => 'Eggs',
        'mounts' => 'Monteringar',
        'alerts' => 'Aviseringar',
        'webhooks' => 'Webhooks',
        'extensions' => 'Tillägg',
        'activity_log' => 'Aktivitetslogg',
        'monitoring' => 'Övervakning',
        'settings' => 'Inställningar',
        'designify' => 'Designify',
        'updates' => 'Programuppdateringar',
    ],
    'actions' => [
        'send_email' => 'Skicka e-post',
        'read' => 'Visa',
        'create' => 'Skapa',
        'update' => 'Redigera',
        'delete' => 'Ta bort',
        'assign' => 'Tilldela användare',
        'manage' => 'Hantera',
        'install' => 'Installera uppdateringar',
    ],
    'messages' => [
        'in_use' => 'Den här rollen är fortfarande tilldelad användare. Ta bort den från dem innan du raderar den.',
    ],
    'user' => [
        'roles' => 'Roller',
        'roles_self' => 'Du kan inte ändra dina egna roller.',
        'none' => 'Ingen',
    ],
];
