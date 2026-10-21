<?php

return [
    'label' => 'Role',
    'plural-label' => 'Roles',
    'all' => 'All',
    'select_all' => 'Select all',
    'deselect_all' => 'Deselect all',
    'details' => [
        'title' => 'Role Details',
        'description' => 'Roles decide what a user can do across the panel, in the admin area and on servers they do not own.',
        'name' => 'Name',
        'name_placeholder' => 'Support Team',
        'description_label' => 'Description',
        'description_placeholder' => 'What this role is meant for.',
    ],
    'table' => [
        'name' => 'Name',
        'permissions' => 'Permissions',
        'users' => 'Users',
        'updated' => 'Last Updated',
    ],
    'groups' => [
        'management' => [
            'title' => 'Management',
            'description' => 'Servers, the people who use them and the infrastructure they run on.',
        ],
        'service' => [
            'title' => 'Service Management',
            'description' => 'What can be installed on a server and how the panel itself is extended and updated.',
        ],
        'administration' => [
            'title' => 'Administration',
            'description' => 'Panel-wide configuration, appearance and insight.',
        ],
    ],
    'resources' => [
        'servers' => 'Servers',
        'users' => 'Users',
        'roles' => 'Roles',
        'nodes' => 'Nodes',
        'locations' => 'Locations',
        'database_hosts' => 'Database Hosts',
        'nests' => 'Nests',
        'eggs' => 'Eggs',
        'mounts' => 'Mounts',
        'alerts' => 'Alerts',
        'webhooks' => 'Webhooks',
        'extensions' => 'Extensions',
        'activity_log' => 'Activity Log',
        'monitoring' => 'Monitoring',
        'settings' => 'Settings',
        'designify' => 'Designify',
        'updates' => 'Software Updates',
    ],
    'actions' => [
        'send_email' => 'Send email',
        'read' => 'View',
        'create' => 'Create',
        'update' => 'Edit',
        'delete' => 'Delete',
        'assign' => 'Assign to users',
        'manage' => 'Manage',
        'install' => 'Install updates',
    ],
    'messages' => [
        'in_use' => 'This role is still assigned to users. Remove it from them before deleting it.',
    ],
    'user' => [
        'roles' => 'Roles',
        'roles_self' => 'You cannot change your own roles.',
        'none' => 'None',
    ],
];
