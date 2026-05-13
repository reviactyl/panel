<?php

return [

    'label' => 'Gunung',
    'plural_label' => 'Gunung',

    'sections' => [
        'configuration' => 'Konfigurasi Pemasangan',
    ],

    'fields' => [
        'name' => 'Nama',
        'description' => 'Keterangan',
        'source' => 'Jalur Sumber',
        'target' => 'Jalur Sasaran',
        'read_only' => 'Hanya Baca',
        'user_mountable' => 'Dapat Dipasang Pengguna',
    ],

    'helpers' => [
        'name' => 'Nama unik yang digunakan untuk memisahkan tunggangan ini dari tunggangan lainnya.',
        'description' => 'Deskripsi gunung ini yang lebih panjang dan dapat dibaca manusia.',
        'source' => 'Jalur file pada mesin host untuk dipasang ke kontainer.',
        'target' => 'Jalur di dalam wadah untuk memasang ini sebagai.',
        'read_only' => 'Jika disetel, mount akan bersifat read-only di dalam container.',
        'user_mountable' => 'Jika disetel, pengguna akan dapat memasangnya ke server mereka.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nama',
        'source' => 'Sumber',
        'target' => 'Target',
        'read_only' => 'Hanya Baca',
        'user_mountable' => 'Dapat Dipasang Pengguna',
    ],

    'actions' => [
        'attach_egg' => 'Lampirkan Telur',
        'attach_node' => 'Lampirkan Node',
    ],

];
