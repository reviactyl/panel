<?php

return [

    'tabs' => [
        'configuration' => 'Konfigurasi Telur',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Konfigurasi',
        ],
        'identity' => [
            'title' => 'Identitas',
        ],
        'docker_images' => [
            'title' => 'Gambar buruh pelabuhan',
            'description' => 'Gambar buruh pelabuhan tersedia untuk server yang menggunakan telur ini. Masukkan satu per baris.',
        ],
        'process_management' => [
            'title' => 'Manajemen Proses',
        ],
        'variables' => [
            'title' => 'Variabel',
        ],
        'install_script' => [
            'title' => 'Instal Skrip',
        ],
    ],

    'fields' => [
        'nest' => 'Sarang',
        'uuid' => 'UUID',
        'name' => 'Nama',
        'author' => 'Pengarang',
        'image' => 'Gambar',
        'description' => 'Keterangan',
        'image_name' => 'Nama Gambar',
        'image_uri' => 'URI Gambar',
        'add_docker_image' => 'Tambahkan Gambar Docker',
        'force_outgoing_ip' => 'Paksa IP Keluar',
        'features' => 'Fitur',
        'startup' => 'Perintah Memulai',
        'config_stop' => 'Hentikan Perintah',
        'config_from' => 'Salin Pengaturan Dari',
        'config_startup' => 'Mulai Konfigurasi (JSON)',
        'config_logs' => 'Konfigurasi Log (JSON)',
        'config_files' => 'File Konfigurasi (JSON)',
        'file_denylist' => 'Daftar Penolakan File',
        'env_variable' => 'Variabel Lingkungan',
        'user_viewable' => 'Pengguna Dapat Melihat',
        'user_editable' => 'Pengguna Dapat Mengedit',
        'rules' => 'Aturan Masukan',
        'default_value' => 'Nilai Bawaan',
        'script_install' => 'Instal Skrip',
        'script_container' => 'Wadah Skrip',
        'script_entry' => 'Perintah Titik Masuk Skrip',
        'copy_script_from' => 'Salin Skrip Dari',
        'script_is_privileged' => 'Keistimewaan',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Memaksa semua lalu lintas jaringan keluar agar IP Sumbernya di-NAT ke IP dari IP alokasi utama server.',
        'features' => 'Fitur tambahan yang dimiliki telur. Berguna untuk mengonfigurasi modifikasi panel tambahan.',
        'file_denylist' => 'File yang tidak boleh diedit oleh pengguna.',
        'script_is_privileged' => 'Jalankan skrip instalasi sebagai wadah istimewa (root).',
    ],

    'actions' => [
        'export' => 'Ekspor',
        'create' => 'Buat Telur',
        'edit' => 'Sunting',
    ],

    'notices' => [
        'cannot_delete' => 'Tidak dapat menghapus telur',
        'cannot_delete_body' => 'Telur ini memiliki server :count yang terkait. Harap hapus atau tetapkan kembali terlebih dahulu.',
        'cannot_delete_multiple' => 'Tidak dapat menghapus telur dengan server',
        'cannot_delete_multiple_body' => 'Telur :count memiliki server terkait dan dilewati.',
    ],

];
