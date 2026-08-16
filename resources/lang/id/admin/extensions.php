<?php

return [

    'label' => 'Perpanjangan',
    'plural-label' => 'Ekstensi',
    'marketplace_heading' => 'Available Extensions',

    'columns' => [
        'icon' => 'Icon',
        'id' => 'ID',
        'name' => 'Nama',
        'version' => 'Versi',
        'author' => 'Pengarang',
        'enabled' => 'Diaktifkan',
        'updated' => 'Diperbarui',
        'last_updated' => 'Last Updated',
        'downloads' => 'Downloads',
        'manifest_json' => 'Manifes JSON',
        'file' => 'Choose a .rext file to upload',
    ],

    'modals' => [
        'manifest' => 'Manifes Ekstensi',
    ],

    'actions' => [
        'edit' => 'Sunting',
        'view' => 'Get Extension',
        'upload' => 'Mengunggah',
        'manifest' => 'Lihat Manifes',
        'disable' => 'Cacat',
        'enable' => 'Memungkinkan',
        'delete' => 'Menghapus',
        'close' => 'Menutup',
    ],

    'alerts' => [
        'enabled' => 'Ekstensi diaktifkan.',
        'enable_failed' => 'Gagal mengaktifkan ekstensi.',
        'disabled' => 'Ekstensi dinonaktifkan.',
        'disable_failed' => 'Gagal menonaktifkan ekstensi.',
        'uninstalled' => 'Ekstensi dicopot pemasangannya.',
        'uninstall_failed' => 'Gagal mencopot pemasangan ekstensi.',
        'could_not_locate_file' => 'Tidak dapat menemukan file paket yang diunggah.',
        'invalid_file_type' => 'Hanya file .rext yang diperbolehkan.',
        'upload_hint' => 'Hanya paket ekstensi .rext yang diperbolehkan.',
        'install_failed' => 'Pemasangan ekstensi gagal.',
        'install_success' => ':name (:version) berhasil diinstal.',
    ],

];
