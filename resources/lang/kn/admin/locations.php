<?php

return [

    'label' => 'ಸ್ಥಳ',
    'plural-label' => 'ಸ್ಥಳಗಳು',

    'section' => [
        'title' => 'ಸ್ಥಳದ ವಿವರಗಳು',
        'description' => 'ನೋಡ್‌ಗಳನ್ನು ನಿಯೋಜಿಸಬಹುದಾದ ಸ್ಥಳವನ್ನು ವಿವರಿಸಿ.',
    ],

    'fields' => [
        'short' => [
            'label' => 'ಕಿರು ಕೋಡ್',
            'placeholder' => 'us.nyc.1',
            'helper' => 'ಈ ಸ್ಥಳಕ್ಕಾಗಿ ಕಿರು ಗುರುತಿಸುವಿಕೆ.',
        ],

        'long' => [
            'label' => 'ವಿವರಣೆ',
            'placeholder' => 'ನ್ಯೂಯಾರ್ಕ್ ನಗರ, NY, USA',
            'helper' => 'ಈ ಸ್ಥಳದ ದೀರ್ಘ ವಿವರಣೆ.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'ಕಿರು ಕೋಡ್',
        'long' => 'ವಿವರಣೆ',
        'nodes' => 'ನೋಡ್ಗಳು',
        'servers' => 'ಸರ್ವರ್‌ಗಳು',
        'created' => 'ರಚಿಸಲಾಗಿದೆ',
    ],

    'actions' => [
        'edit' => 'ಸಂಪಾದಿಸು',
        'delete' => 'ಅಳಿಸಿ',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'ಸಂಯೋಜಿತ ನೋಡ್‌ಗಳೊಂದಿಗೆ ಸ್ಥಳವನ್ನು ಅಳಿಸಲು ಸಾಧ್ಯವಿಲ್ಲ.',
    ],

];
