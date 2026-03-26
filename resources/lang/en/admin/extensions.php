<?php

return [

    'label' => 'Extension',
    'plural-label' => 'Extensions',

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'version' => 'Version',
        'author' => 'Author',
        'enabled' => 'Enabled',
        'updated' => 'Updated',
        'manifest_json' => 'Manifest JSON',
    ],

    'modals' => [
        'manifest' => 'Extension Manifest',
    ],

    'actions' => [
        'edit' => 'Edit',
        'upload' => 'Upload',
        'manifest' => 'View Manifest',
        'disable' => 'Disable',
        'enable' => 'Enable',
        'delete' => 'Delete',
        'close' => 'Close',
    ],

    'alerts' => [
        'enabled' => 'Extension enabled.',
        'enable_failed' => 'Failed to enable extension.',
        'disabled' => 'Extension disabled.',
        'disable_failed' => 'Failed to disable extension.',
        'uninstalled' => 'Extension uninstalled.',
        'uninstall_failed' => 'Failed to uninstall extension.',
        'could_not_locate_file' => 'Could not locate uploaded package file.',
        'invalid_file_type' => 'Only .rext files are allowed.',
        'install_failed' => 'Extension install failed.',
        'install_success' => 'Installed :name (:version) successfully.',
    ],

];
