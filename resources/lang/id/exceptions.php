<?php

return [
    'daemon_connection_failed' => 'Terjadi pengecualian saat mencoba berkomunikasi dengan daemon yang menghasilkan kode respons HTTP/:code. Pengecualian ini telah dicatat.',
    'node' => [
        'servers_attached' => 'Sebuah node tidak boleh memiliki server yang terhubung dengannya agar dapat dihapus.',
        'daemon_off_config_updated' => 'Konfigurasi daemon telah diperbarui, namun terjadi kesalahan saat mencoba memperbarui file konfigurasi pada Daemon secara otomatis. Anda perlu memperbarui file konfigurasi (config.yml) secara manual agar daemon dapat menerapkan perubahan ini.',
    ],
    'allocations' => [
        'server_using' => 'Sebuah server sedang menggunakan alokasi ini. Alokasi hanya dapat dihapus jika tidak ada server yang sedang menggunakannya.',
        'too_many_ports' => 'Menambahkan lebih dari 1000 port dalam satu rentang sekaligus tidak didukung.',
        'invalid_mapping' => 'Pemetaan yang diberikan untuk :port tidak valid dan tidak dapat diproses.',
        'cidr_out_of_range' => 'Notasi CIDR hanya mengizinkan mask antara /25 dan /32.',
        'port_out_of_range' => 'Port dalam alokasi harus lebih besar dari 1024 dan kurang dari atau sama dengan 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Nest dengan server aktif yang terhubung tidak dapat dihapus dari Panel.',
        'egg' => [
            'delete_has_servers' => 'Egg dengan server aktif yang terhubung tidak dapat dihapus dari Panel.',
            'invalid_copy_id' => 'Egg yang dipilih untuk menyalin skrip tidak ada, atau sedang menyalin skrip itu sendiri.',
            'must_be_child' => 'Direktif "Salin Pengaturan Dari" untuk Egg ini harus merupakan opsi anak untuk Nest yang dipilih.',
            'has_children' => 'Egg ini adalah induk dari satu atau lebih Egg lainnya. Silakan hapus Egg tersebut sebelum menghapus Egg ini.',
        ],
        'variables' => [
            'env_not_unique' => 'Variabel lingkungan :name harus unik untuk Egg ini.',
            'reserved_name' => 'Variabel lingkungan :name dilindungi dan tidak dapat ditetapkan ke variabel.',
            'bad_validation_rule' => 'Aturan validasi ":rule" bukan aturan yang valid untuk aplikasi ini.',
        ],
        'importer' => [
            'json_error' => 'Terjadi kesalahan saat mencoba mem-parsing file JSON: :error.',
            'file_error' => 'File JSON yang diberikan tidak valid.',
            'invalid_json_provided' => 'File JSON yang diberikan tidak dalam format yang dapat dikenali.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Mengedit akun subpengguna Anda sendiri tidak diizinkan.',
        'user_is_owner' => 'Anda tidak dapat menambahkan pemilik server sebagai subpengguna untuk server ini.',
        'subuser_exists' => 'Pengguna dengan alamat email tersebut sudah ditetapkan sebagai subpengguna untuk server ini.',
    ],
    'subuser_preview' => [
        'start_blocked' => 'Anda tidak dapat memulai pratinjau lain selama mode pratinjau masih aktif.',
        'owner_only' => 'Hanya pemilik server yang dapat melihat pratinjau subpengguna.',
        'session_unavailable' => 'Sesi pratinjau ini sudah tidak tersedia lagi.',
        'session_expired' => 'Sesi pratinjau ini telah kedaluwarsa.',
        'concurrent_start' => 'Sesi pratinjau sudah dimulai.',
        'account_unavailable' => 'Informasi akun tidak tersedia selama pratinjau subpengguna.',
        'categories_unavailable' => 'Kategori server pribadi tidak tersedia selama masa pratinjau subpengguna.',
        'permission_denied' => 'Anda tidak memiliki izin untuk melakukan tindakan ini di tampilan pratinjau.',
        'resource_unavailable' => 'Sumber daya ini tidak tersedia selama masa pratinjau subpengguna.',
        'live_connection_unavailable' => 'Koneksi langsung ini tidak tersedia selama masa pratinjau subpengguna.',
        'file_not_found' => 'File yang diminta tidak ada dalam pratinjau ini.',
        'file_too_large' => 'Ukuran berkas ini terlalu besar untuk ditampilkan dalam pratinjau.',
        'state_too_large' => 'Pratinjau ini telah mencapai batas penyimpanannya.',
        'unsafe_pull_url' => 'Hanya URL HTTPS yang aman yang dapat dimuat ke dalam pratinjau.',
        'action_unavailable' => 'Tindakan ini tidak tersedia selama masa pratinjau subpengguna.',
        'database_limit' => 'Server ini telah mencapai batas kapasitas basis datanya.',
        'database_host_unavailable' => 'Tidak ada host basis data yang tersedia untuk server ini.',
        'task_limit' => 'Jadwal ini telah mencapai batas tugasnya.',
        'allocation_limit' => 'Server ini telah mencapai batas alokasinya.',
        'allocation_unavailable' => 'Tidak ada alokasi tambahan yang tersedia untuk server ini.',
        'primary_allocation' => 'Alokasi utama tidak dapat dihapus.',
        'backup_limit' => 'Server ini telah mencapai batas kapasitas pencadangannya.',
        'locked_backup' => 'Cadangan yang terkunci tidak dapat dihapus.',
        'variable_unavailable' => 'Variabel lingkungan tersebut tidak tersedia atau hanya dapat dibaca.',
        'docker_image_unavailable' => 'Gambar Docker yang dipilih tidak tersedia untuk server ini.',
    ],
    'databases' => [
        'delete_has_databases' => 'Tidak dapat menghapus server host database yang memiliki database aktif yang terhubung dengannya.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'Waktu interval maksimum untuk tugas berantai adalah 15 menit.',
    ],
    'locations' => [
        'has_nodes' => 'Tidak dapat menghapus lokasi yang memiliki node aktif yang terhubung dengannya.',
    ],
    'users' => [
        'node_revocation_failed' => 'Gagal mencabut kunci pada <a href=":link">Node #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Tidak ditemukan node yang memenuhi persyaratan yang ditentukan untuk penyebaran otomatis.',
        'no_viable_allocations' => 'Tidak ditemukan alokasi yang memenuhi persyaratan untuk penyebaran otomatis.',
    ],
    'api' => [
        'resource_not_found' => 'Sumber daya yang diminta tidak ada di server ini.',
    ],
    'social' => [
        'unlink_only_login' => 'Anda tidak dapat memutuskan tautan satu-satunya metode login Anda tanpa menetapkan kata sandi terlebih dahulu.',
    ],
];
