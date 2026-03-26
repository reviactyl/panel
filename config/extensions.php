<?php

return [
    'storage' => [
        'public_path' => public_path('extensions'),
        'public_fs_path' => storage_path('extensions'),
        'temp_path' => storage_path('app/extensions/tmp'),
    ],

    'security' => [
        'enforce_compatibility' => true,
        'allow_remote_installs' => true,
    ],
];
