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
        'created' => 'Sebuah nest baru, :name, telah berhasil dibuat.',
        'deleted' => 'Berhasil menghapus nest yang diminta dari Panel.',
        'updated' => 'Berhasil memperbarui opsi konfigurasi nest.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Berhasil mengimpor Egg ini dan variabel terkaitnya.',
            'updated_via_import' => 'Egg ini telah diperbarui menggunakan file yang disediakan.',
            'deleted' => 'Berhasil menghapus egg yang diminta dari Panel.',
            'updated' => 'Konfigurasi Egg telah berhasil diperbarui.',
            'script_updated' => 'Skrip instal egg telah diperbarui dan akan berjalan setiap kali server diinstal.',
            'egg_created' => 'Sebuah egg baru berhasil dibuat. Anda perlu merestart daemon yang berjalan untuk menerapkan egg baru ini.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'Variabel ":variable" telah dihapus dan tidak akan tersedia lagi untuk server setelah rebuild.',
            'variable_updated' => 'Variabel ":variable" telah diperbarui. Anda perlu me-rebuild server yang menggunakan variabel ini untuk menerapkan perubahan.',
            'variable_created' => 'Variabel baru berhasil dibuat dan ditugaskan ke egg ini.',
        ],
    ],
];
