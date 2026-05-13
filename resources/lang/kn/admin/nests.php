<?php

return [
    
    'label' => 'Nest',
    'plural_label' => 'Nests',

    'sections' => [
        'configuration' => 'Nest Configuration',
    ],

    'fields' => [
        'name' => 'Name',
        'author' => 'Author',
        'description' => 'Description',
    ],

    'helpers' => [
        'name' => 'A unique name used to identify this nest.',
        'author' => 'The author of this nest. Must be a valid email.',
        'description' => 'A description of this nest.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'author' => 'Author',
        'eggs' => 'Eggs',
        'servers' => 'Servers',
    ],
    
    'actions' => [
        'import' => 'Import Egg',
    ],

    'import' => [
        'file_label' => 'Egg File (JSON)',
        'nest_label' => 'Associated Nest',
        'file_not_found' => 'File not found',
        'file_not_found_body' => 'Could not locate uploaded file.',
        'invalid_format' => 'Invalid file format',
        'invalid_format_body' => 'Unexpected file format received.',
        'success' => 'Egg imported successfully',
        'failed' => 'Failed to import egg',
    ],

    'notices' => [
        'created' => 'ಹೊಸ ನೆಸ್ಟ್ :name ಅನ್ನು ಯಶಸ್ವಿಯಾಗಿ ರಚಿಸಲಾಗಿದೆ.',
        'deleted' => 'ಕೋರಿರುವ ನೆಸ್ಟ್ ಅನ್ನು ಪ್ಯಾನೆಲ್‌ನಿಂದ ಯಶಸ್ವಿಯಾಗಿ ಅಳಿಸಲಾಗಿದೆ.',
        'updated' => 'ನೆಸ್ಟ್ ಸಂರಚನಾ ಆಯ್ಕೆಗಳನ್ನು ಯಶಸ್ವಿಯಾಗಿ ನವೀಕರಿಸಲಾಗಿದೆ.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'ಈ ಎಗ್ ಮತ್ತು ಅದರ ಸಂಬಂಧಿತ ವೇರಿಯಬಲ್‌ಗಳನ್ನು ಯಶಸ್ವಿಯಾಗಿ ಆಮದು ಮಾಡಲಾಗಿದೆ.',
            'updated_via_import' => 'ನೀಡಲಾದ ಫೈಲ್ ಬಳಸಿ ಈ ಎಗ್ ಅನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ.',
            'deleted' => 'ಕೋರಿರುವ ಎಗ್ ಅನ್ನು ಪ್ಯಾನೆಲ್‌ನಿಂದ ಯಶಸ್ವಿಯಾಗಿ ಅಳಿಸಲಾಗಿದೆ.',
            'updated' => 'ಎಗ್ ಸಂರಚನೆಯನ್ನು ಯಶಸ್ವಿಯಾಗಿ ನವೀಕರಿಸಲಾಗಿದೆ.',
            'script_updated' => 'ಎಗ್ ಇನ್‌ಸ್ಟಾಲ್ ಸ್ಕ್ರಿಪ್ಟ್ ನವೀಕರಿಸಲಾಗಿದೆ ಮತ್ತು ಸರ್ವರ್‌ಗಳನ್ನು ಸ್ಥಾಪಿಸುವಾಗಲೆಲ್ಲಾ ಇದು ಕಾರ್ಯನಿರ್ವಹಿಸುತ್ತದೆ.',
            'egg_created' => 'ಹೊಸ ಎಗ್ ಅನ್ನು ಯಶಸ್ವಿಯಾಗಿ ರಚಿಸಲಾಗಿದೆ. ಈ ಹೊಸ ಎಗ್ ಅನ್ನು ಅನ್ವಯಿಸಲು ಪ್ರಸ್ತುತ ಚಾಲನೆಯಲ್ಲಿರುವ ಯಾವುದೇ ಡೀಮನ್‌ಗಳನ್ನು ಮರುಪ್ರಾರಂಭಿಸಬೇಕು.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => '":variable" ವೇರಿಯಬಲ್ ಅನ್ನು ಅಳಿಸಲಾಗಿದೆ ಮತ್ತು ಮರುನಿರ್ಮಾಣದ ನಂತರ ಇದು ಸರ್ವರ್‌ಗಳಿಗೆ ಲಭ್ಯವಿರುವುದಿಲ್ಲ.',
            'variable_updated' => '":variable" ವೇರಿಯಬಲ್ ಅನ್ನು ನವೀಕರಿಸಲಾಗಿದೆ. ಬದಲಾವಣೆಗಳನ್ನು ಅನ್ವಯಿಸಲು ಈ ವೇರಿಯಬಲ್ ಬಳಸುತ್ತಿರುವ ಯಾವುದೇ ಸರ್ವರ್‌ಗಳನ್ನು ಮರುನಿರ್ಮಿಸಬೇಕು.',
            'variable_created' => 'ಹೊಸ ವೇರಿಯಬಲ್ ಅನ್ನು ಯಶಸ್ವಿಯಾಗಿ ರಚಿಸಿ ಈ ಎಗ್‌ಗೆ ನಿಯೋಜಿಸಲಾಗಿದೆ.',
        ],
    ],
];
