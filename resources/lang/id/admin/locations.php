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
            'placeholder' => 'kami.nyc.1',
            'helper' => 'Pengidentifikasi singkat untuk lokasi ini.',
        ],

        'long' => [
            'label' => 'Keterangan',
            'placeholder' => 'Kota New York, NY, AS',
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
