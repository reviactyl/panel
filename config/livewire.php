<?php

return [
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:20480'],
    ],

    'payload' => [
        'max_size' => 1024 * 1024,
        // Rich-editor tables inside action modals exceed Livewire's default depth.
        'max_nesting_depth' => 32,
        'max_calls' => 50,
        'max_components' => 200,
    ],
];
