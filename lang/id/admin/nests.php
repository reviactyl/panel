<?php

return [

    'label' => 'Sarang',
    'plural_label' => 'Sarang',

    'sections' => [
        'configuration' => 'Konfigurasi Sarang',
    ],

    'fields' => [
        'name' => 'Nama',
        'author' => 'Pengarang',
        'description' => 'Keterangan',
    ],

    'helpers' => [
        'name' => 'Nama unik yang digunakan untuk mengidentifikasi sarang ini.',
        'author' => 'Penulis sarang ini. Harus berupa email yang valid.',
        'description' => 'Deskripsi sarang ini.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nama',
        'author' => 'Pengarang',
        'eggs' => 'telur',
        'servers' => 'Server',
    ],

    'actions' => [
        'import' => 'Impor Telur',
    ],

    'import' => [
        'file_label' => 'File Telur (JSON)',
        'nest_label' => 'Sarang Terkait',
        'file_not_found' => 'Berkas tidak ditemukan',
        'file_not_found_body' => 'Tidak dapat menemukan file yang diunggah.',
        'invalid_format' => 'Format berkas tidak valid',
        'invalid_format_body' => 'Format file tak terduga diterima.',
        'success' => 'Telur berhasil diimpor',
        'failed' => 'Gagal mengimpor telur',
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
