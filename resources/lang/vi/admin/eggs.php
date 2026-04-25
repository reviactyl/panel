<?php

return [

    'tabs' => [
        'configuration' => 'Egg Configuration',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Configuration',
        ],
        'identity' => [
            'title' => 'Identity',
        ],
        'docker_images' => [
            'title' => 'Docker Images',
            'description' => 'The docker images available to servers using this egg. Enter one per line.',
        ],
        'process_management' => [
            'title' => 'Process Management',
        ],
        'variables' => [
            'title' => 'Variables',
        ],
        'install_script' => [
            'title' => 'Install Script',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'image_name' => 'Image Name',
        'image_uri' => 'Image URI',
        'add_docker_image' => 'Add Docker Image',
        'force_outgoing_ip' => 'Force Outgoing IP',
        'features' => 'Features',
        'startup' => 'Startup Command',
        'config_stop' => 'Stop Command',
        'config_from' => 'Copy Settings From',
        'config_startup' => 'Start Configuration (JSON)',
        'config_logs' => 'Log Configuration (JSON)',
        'config_files' => 'Configuration Files (JSON)',
        'file_denylist' => 'File Denylist',
        'env_variable' => 'Environment Variable',
        'user_viewable' => 'Users Can View',
        'user_editable' => 'Users Can Edit',
        'rules' => 'Input Rules',
        'script_install' => 'Install Script',
        'script_container' => 'Script Container',
        'script_entry' => 'Script Entrypoint Command',
        'copy_script_from' => 'Copy Script From',
        'script_is_privileged' => 'Privileged',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Forces all outgoing network traffic to have its Source IP NATed to the IP of the server\'s primary allocation IP.',
        'features' => 'Additional features belonging to the egg. Useful for configuring additional panel modifications.',
        'file_denylist' => 'Files that should not be edited by the user.',
        'script_is_privileged' => 'Run the install script as a privileged container (root).',
    ],

    'actions' => [
        'export' => 'Export',
        'create' => 'Create Egg',
        'edit' => 'Edit',
    ],

    'notices' => [
        'cannot_delete' => 'Cannot delete egg',
        'cannot_delete_body' => 'This egg has :count server(s) associated. Please delete or reassign them first.',
        'cannot_delete_multiple' => 'Cannot delete eggs with servers',
        'cannot_delete_multiple_body' => ':count egg(s) have associated servers and were skipped.',
    ],

];
