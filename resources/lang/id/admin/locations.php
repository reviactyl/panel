<?php

return [

    'label' => 'Lokasi',
    'plural-label' => 'Lokasi',

    'section' => [
        'title' => 'Detail Lokasi',
        'description' => 'Tentukan lokasi di mana node dapat ditugaskan.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Kode Pendek',
            'helper' => 'Pengidentifikasi singkat untuk lokasi ini.',
        ],

        'long' => [
            'label' => 'Keterangan',
            'helper' => 'Deskripsi yang lebih panjang tentang lokasi ini.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Kode Pendek',
        'long' => 'Keterangan',
        'nodes' => 'Node',
        'servers' => 'Server',
        'created' => 'Dibuat',
    ],

    'actions' => [
        'edit' => 'Sunting',
        'delete' => 'Menghapus',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Tidak dapat menghapus lokasi dengan node terkait.',
    ],

];
