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
        'start_blocked' => 'You cannot start another preview while preview mode is active.',
        'owner_only' => 'Only the server owner can preview a subuser.',
        'session_unavailable' => 'This preview session is no longer available.',
        'session_expired' => 'This preview session has expired.',
        'concurrent_start' => 'A preview session is already being started.',
        'account_unavailable' => 'Account information is unavailable during subuser preview.',
        'categories_unavailable' => 'Personal server categories are unavailable during subuser preview.',
        'permission_denied' => 'You do not have permission to perform this action in the preview.',
        'resource_unavailable' => 'This resource is unavailable during subuser preview.',
        'live_connection_unavailable' => 'This live connection is unavailable during subuser preview.',
        'file_not_found' => 'The requested file does not exist in this preview.',
        'file_too_large' => 'This file is too large to store in the preview.',
        'state_too_large' => 'This preview has reached its storage limit.',
        'unsafe_pull_url' => 'Only safe HTTPS URLs can be pulled into the preview.',
        'action_unavailable' => 'This action is not available during subuser preview.',
        'database_limit' => 'This server has reached its database limit.',
        'database_host_unavailable' => 'No database host is available for this server.',
        'task_limit' => 'This schedule has reached its task limit.',
        'allocation_limit' => 'This server has reached its allocation limit.',
        'allocation_unavailable' => 'No additional allocation is available for this server.',
        'primary_allocation' => 'The primary allocation cannot be removed.',
        'backup_limit' => 'This server has reached its backup limit.',
        'locked_backup' => 'A locked backup cannot be deleted.',
        'variable_unavailable' => 'The environment variable is unavailable or read-only.',
        'docker_image_unavailable' => 'The selected Docker image is unavailable for this server.',
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
