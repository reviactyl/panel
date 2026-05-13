<?php

return [

    'label' => 'Basis data',
    'plural-label' => 'Basis Data',

    'none' => 'Tidak ada',

    'sections' => [
        'host_details' => [
            'title' => 'Detail Tuan Rumah',
            'description' => 'Konfigurasikan pengaturan koneksi host database.',
        ],

        'authentication' => [
            'title' => 'Otentikasi',
        ],

        'linked_node' => [
            'title' => 'Node Tertaut',
        ],
    ],

    'placeholders' => [
        'name' => 'MySQL Produksi',
        'host' => '127.0.0.1',
        'username' => 'reviactyl',
    ],

    'helpers' => [
        'host' => 'Nama host atau alamat IP server database.',
        'linked_node' => 'Opsional. Tautkan host ini ke node tertentu.',
    ],

    'fields' => [
        'linked_node' => 'Node Tertaut',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nama',
        'host' => 'Tuan rumah',
        'port' => 'Pelabuhan',
        'username' => 'Nama belakang',
        'linked_node' => 'Node Tertaut',
        'databases' => 'Basis Data',
        'created' => 'Dibuat',
    ],

    'actions' => [
        'edit' => 'Sunting',
        'delete' => 'Menghapus',
    ],

    'errors' => [
        'cannot_delete' => 'Tidak dapat menghapus host database dengan database terkait.',
    ],

];
