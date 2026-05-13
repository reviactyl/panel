<?php

return [
    'label' => 'simpul',
    'plural-label' => 'simpul',

    'sections' => [
        'overview' => [
            'title' => 'Ringkasan',
            'information-label' => 'Informasi Node',
            'version-label' => 'Versi Agen',
            'architecture-label' => 'Arsitektur',
            'kernel-label' => 'Inti',
            'cpus-label' => 'Utas CPU',
            'cpu-usage-label' => 'Penggunaan CPU',
            'memory-usage-label' => 'Penggunaan Memori',
            'disk-usage-label' => 'Penggunaan Disk',
        ],
        'tabs' => [
            'title' => 'Konfigurasi Node',
        ],
        'identity' => [
            'title' => 'Identitas',
            'description' => 'Informasi simpul dasar.',
        ],
        'connection' => [
            'title' => 'Detail Koneksi',
            'description' => 'Konfigurasikan cara terhubung ke node ini.',
        ],
        'resources' => [
            'title' => 'Alokasi Sumber Daya',
            'description' => 'Tentukan batas memori dan disk untuk node ini.',
        ],
        'daemon' => [
            'title' => 'Konfigurasi Daemon',
            'description' => 'Konfigurasikan pengaturan khusus daemon.',
        ],
        'configuration' => [
            'title' => 'Konfigurasi',
            'config_description' => 'File Konfigurasi',
            'deploy_description' => 'Hasilkan perintah penerapan khusus yang dapat digunakan untuk mengonfigurasi Agen di server target.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Publik',
            'helper' => 'Dengan menyetel sebuah simpul ke pribadi, Anda akan menolak kemampuan penerapan otomatis ke simpul ini.',
        ],
        'name' => [
            'label' => 'Nama',
            'placeholder' => 'Nama Node',
            'helper' => 'Nama deskriptif untuk node ini.',
        ],
        'description' => [
            'label' => 'Keterangan',
            'placeholder' => 'Deskripsi simpul',
            'helper' => 'Deskripsi opsional untuk simpul ini.',
        ],
        'location' => [
            'label' => 'Lokasi',
            'helper' => 'Lokasi dimana node ini ditugaskan.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'Nama domain atau alamat IP yang sepenuhnya memenuhi syarat.',
        ],
        'ssl' => [
            'label' => 'Menggunakan SSL',
            'helper' => 'Apakah daemon pada node ini dikonfigurasi untuk menggunakan SSL untuk komunikasi yang aman.',
            'helper_forced' => 'Panel ini berjalan pada HTTPS, jadi SSL dipaksakan untuk node ini.',
        ],
        'behind_proxy' => [
            'label' => 'Dibalik Proksi',
            'helper' => 'Aktifkan jika node ini berada di belakang proxy seperti Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Modus Pemeliharaan',
            'helper' => 'Cegah pembuatan server baru di node ini.',
        ],
        'memory' => [
            'label' => 'Jumlah Memori',
            'helper' => 'Total memori dalam MiB tersedia pada node ini.',
        ],
        'memory_overallocate' => [
            'label' => 'Alokasi Memori Secara Keseluruhan',
            'helper' => 'Persentase memori untuk dialokasikan secara keseluruhan. Gunakan -1 untuk menonaktifkan pemeriksaan.',
        ],
        'disk' => [
            'label' => 'Total Ruang Disk',
            'helper' => 'Total ruang disk di MiB tersedia di node ini.',
        ],
        'disk_overallocate' => [
            'label' => 'Alokasi Disk Secara Keseluruhan',
            'helper' => 'Persentase disk yang akan dialokasikan secara keseluruhan. Gunakan -1 untuk menonaktifkan pemeriksaan.',
        ],
        'upload_size' => [
            'label' => 'Ukuran Unggahan Maks',
            'helper' => 'Ukuran unggah file maksimum yang diperbolehkan melalui panel web.',
        ],
        'daemon_base' => [
            'label' => 'Direktori Dasar',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Direktori tempat file server disimpan.',
        ],
        'daemon_listen' => [
            'label' => 'Pelabuhan Daemon',
            'helper' => 'Port yang didengarkan daemon untuk komunikasi HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'Pelabuhan SFTP',
            'helper' => 'Port yang digunakan untuk koneksi SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'ID Token',
        ],
        'container_text' => [
            'label' => 'Awalan Kontainer',
            'helper' => 'Awalan teks ditampilkan dalam nama kontainer.',
        ],
    ],

    'table' => [
        'health' => 'Kesehatan',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'periksa konsol browser',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Nama',
        'location' => 'Lokasi',
        'fqdn' => 'FQDN',
        'scheme' => 'Protokol',
        'public' => 'Publik',
        'behind_proxy' => 'Dibalik Proksi',
        'maintenance_mode' => 'Pemeliharaan',
        'memory' => 'Ingatan',
        'memory_overallocate' => 'Memori Berakhir',
        'disk' => 'Disk',
        'disk_overallocate' => 'Disk Selesai',
        'upload_size' => 'Ukuran Unggah',
        'daemon_listen' => 'Pelabuhan Daemon',
        'daemon_sftp' => 'Pelabuhan SFTP',
        'daemon_base' => 'Direktori Dasar',
        'servers' => 'pelayan',
        'created' => 'Dibuat',
        'updated' => 'Diperbarui',
    ],

    'filters' => [
        'public' => 'Publik',
        'maintenance' => 'Pemeliharaan',
        'public_true' => 'Publik',
        'public_false' => 'Pribadi',
        'maintenance_true' => 'Sedang dalam Pemeliharaan',
        'maintenance_false' => 'Aktif',
    ],

    'actions' => [
        'create' => 'Membuat',
        'edit' => 'Sunting',
        'delete' => 'Menghapus',
        'view' => 'Melihat',
        'random' => 'Acak',
        'view_monitoring' => 'Lihat Pemantauan',
    ],

    'deployment' => [
        'generate_label' => 'Hasilkan Token Penerapan',
        'modal_heading' => 'Perintah Penerapan Otomatis',
        'modal_description' => 'Jalankan perintah ini pada node Anda untuk mengonfigurasi Agen secara otomatis.',
        'modal_close' => 'Menutup',
        'command_label' => 'Komando Penempatan',
        'command_helper' => 'Salin dan jalankan perintah ini di server node Anda.',
        'token_success' => 'Token Berhasil Dihasilkan',
        'token_success_body' => 'Salin dan jalankan perintah di bawah ini pada node Anda.',
        'save_first' => 'Silakan simpan nodenya terlebih dahulu.',
        'auto_generated_key' => 'Kunci penerapan node yang dibuat secara otomatis.',
        'error' => 'Terjadi kesalahan saat membuat token. Silakan coba lagi.',
    ],

    'general' => [
        'na' => 'T/A',
        'unavailable' => 'Tidak tersedia',
    ],

    'messages' => [
        'created' => 'Node telah berhasil dibuat.',
        'updated' => 'Node telah berhasil diperbarui.',
        'deleted' => 'Node telah berhasil dihapus.',
        'cannot_delete_with_servers' => 'Tidak dapat menghapus node dengan server aktif.',
    ],

    'allocations' => [
        'label' => 'Alokasi',
        'table' => [
            'ip' => 'AKU P',
            'port' => 'Pelabuhan',
            'alias' => 'Alias',
            'server' => 'pelayan',
            'notes' => 'Catatan',
            'created' => 'Dibuat',
            'unassigned' => 'Belum ditetapkan',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Address',
                'helper' => 'Mendukung IP tunggal atau CIDR (misalnya 192.0.2.1 atau 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Pelabuhan',
                'helper' => 'Masukkan port atau rentang (misalnya 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP Alias',
                'helper' => 'Alias ​​opsional untuk ditampilkan sebagai pengganti IP.',
            ],
        ],
        'actions' => [
            'add' => 'Tambahkan Alokasi',
            'delete' => 'Menghapus',
        ],
        'messages' => [
            'created' => 'Alokasi ditambahkan.',
            'deleted' => 'Alokasi dihapus.',
            'failed' => 'Tindakan alokasi gagal.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'FQDN atau alamat IP yang diberikan tidak dapat diselesaikan ke alamat IP yang valid.',
        'fqdn_required_for_ssl' => 'Nama domain yang sepenuhnya memenuhi syarat (FQDN) yang merujuk ke alamat IP publik diperlukan untuk menggunakan SSL pada node ini.',
    ],
    'notices' => [
        'allocations_added' => 'Alokasi telah berhasil ditambahkan ke node ini.',
        'node_deleted' => 'Node telah berhasil dihapus dari panel.',
        'location_required' => 'Anda harus memiliki setidaknya satu lokasi yang dikonfigurasi sebelum Anda dapat menambahkan node ke panel ini.',
        'node_created' => 'Berhasil membuat simpul baru. Anda dapat secara otomatis mengkonfigurasi daemon pada mesin ini dengan mengunjungi tab \'Konfigurasi\'. Sebelum Anda dapat menambahkan server apa pun, Anda harus mengalokasikan setidaknya satu alamat IP dan port terlebih dahulu.',
        'node_updated' => 'Informasi node telah diperbarui. Jika pengaturan daemon diubah, Anda perlu me-rebootnya agar perubahan tersebut berlaku.',
        'unallocated_deleted' => 'Menghapus semua port yang tidak dialokasikan untuk <code>:ip</code>.',
    ],
];
