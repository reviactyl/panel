<?php

return [
    'label' => 'pelayan',
    'plural-label' => 'pelayan',

    'sections' => [
        'identity' => [
            'title' => 'Identitas',
            'description' => 'Informasi dan kepemilikan server dasar.',
        ],
        'allocation' => [
            'title' => 'Alokasi',
            'description' => 'Pilih node dan alokasi jaringan untuk server ini.',
        ],
        'startup' => [
            'title' => 'Rintisan',
            'description' => 'Konfigurasikan telur, perintah startup, dan image Docker.',
        ],
        'resources' => [
            'title' => 'Batasan Sumber Daya',
            'description' => 'Tentukan batas sumber daya server.',
        ],
        'feature_limits' => [
            'title' => 'Batasan Fitur',
            'description' => 'Batasi database, alokasi, dan cadangan.',
        ],
        'environment' => [
            'title' => 'Variabel Lingkungan',
            'description' => 'Tetapkan nilai lingkungan untuk telur yang dipilih.',
        ],
    ],

    'status' => [
        'online' => 'On line',
        'offline' => 'Luring',
        'starting' => 'Mulai',
        'stopping' => 'Henti',
        'crashed' => 'Hancur',
        'installing' => 'Menginstal',
        'restoring_backup' => 'Memulihkan Cadangan',
        'install_failed' => 'Pemasangan Gagal',
        'reinstall_failed' => 'Instal Ulang Gagal',
        'suspended' => 'Tergantung',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Detail Inti',
            'allocation' => 'Manajemen Alokasi',
            'feature_limits' => 'Batasan Fitur Aplikasi',
            'resources' => 'Manajemen Sumber Daya',
            'nest' => 'Konfigurasi Sarang',
            'docker' => 'Konfigurasi Docker',
            'startup' => 'Konfigurasi Permulaan',
            'variables' => 'Variabel Layanan',
        ],

        'fields' => [
            'name' => [
                'label' => 'Nama Server',
                'placeholder' => 'Nama Server',
                'helper' => 'Batasan karakter: a-z A-Z 0-9 _ - . dan spasi.',
            ],
            'owner' => [
                'label' => 'Pemilik Server',
                'helper' => 'Alamat email Pemilik Server.',
            ],
            'description' => [
                'label' => 'Deskripsi Server',
                'helper' => 'Penjelasan singkat tentang server ini.',
            ],
            'start_on_completion' => [
                'label' => 'Mulai Server saat Diinstal',
            ],
            'node' => [
                'label' => 'simpul',
                'helper' => 'Node tempat server ini akan dikerahkan.',
            ],
            'allocation' => [
                'label' => 'Alokasi Default',
                'helper' => 'Alokasi utama yang akan ditetapkan ke server ini.',
            ],
            'additional_allocations' => [
                'label' => 'Alokasi Tambahan',
                'helper' => 'Alokasi tambahan untuk ditetapkan ke server ini pada saat pembuatan.',
            ],
            'database_limit' => [
                'label' => 'Batas Basis Data',
                'helper' => 'Jumlah total database yang boleh dibuat oleh pengguna untuk server ini.',
            ],
            'allocation_limit' => [
                'label' => 'Batas Alokasi',
                'helper' => 'Jumlah total alokasi yang boleh dibuat oleh pengguna untuk server ini.',
            ],
            'backup_limit' => [
                'label' => 'Batas Cadangan',
                'helper' => 'Jumlah total cadangan yang dapat dibuat untuk server ini.',
            ],
            'cpu' => [
                'label' => 'Batas CPU',
                'helper' => 'Tetapkan 0 tanpa batas CPU. Inti virtual penuh adalah 100%.',
            ],
            'threads' => [
                'label' => 'Penyematan CPU',
                'helper' => 'Lanjutan: gunakan satu angka atau daftar yang dipisahkan koma, misalnya 0, 0-1,3, atau 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Ingatan',
                'helper' => 'Jumlah memori maksimum yang diperbolehkan untuk penampung ini. Tetapkan 0 untuk tidak terbatas.',
            ],
            'swap' => [
                'label' => 'Menukar',
                'helper' => 'Setel 0 untuk menonaktifkan pertukaran, atau -1 untuk mengizinkan pertukaran tanpa batas.',
            ],
            'disk' => [
                'label' => 'Ruang Disk',
                'helper' => 'Setel 0 untuk mengizinkan penggunaan disk tanpa batas.',
            ],
            'io' => [
                'label' => 'Blokir IO Berat',
                'helper' => 'Lanjutan: Performa IO relatif terhadap container lain yang sedang berjalan. Nilainya harus 10 hingga 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Aktifkan Pembunuh OOM',
                'helper' => 'Menghentikan server jika melanggar batas memori.',
            ],
            'nest' => [
                'label' => 'Sarang',
                'helper' => 'Pilih Nest tempat server ini akan dikelompokkan.',
            ],
            'egg' => [
                'label' => 'Telur',
                'helper' => 'Pilih Egg yang akan menentukan bagaimana server ini harus beroperasi.',
            ],
            'skip_scripts' => [
                'label' => 'Lewati Skrip Pemasangan Telur',
                'helper' => 'Jika Egg yang dipilih memiliki skrip instalasi yang terpasang padanya, skrip akan berjalan selama instalasi kecuali ini dicentang.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Pilih gambar dari dropdown, atau masukkan gambar khusus di bawah.',
            ],
            'custom_image' => [
                'label' => 'Gambar Docker Kustom',
                'placeholder' => 'Atau masukkan gambar khusus...',
                'helper' => 'Ini adalah image Docker default yang akan digunakan untuk menjalankan server ini.',
            ],
            'startup' => [
                'label' => 'Perintah Memulai',
                'helper' => 'Pengganti yang tersedia: {{SERVER_MEMORY}}, {{SERVER_IP}}, dan {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Pilih telur untuk mengonfigurasi variabel layanan',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Modus Lanjutan',
            'helper' => 'Beralih untuk menampilkan opsi konfigurasi server tambahan. Aktifkan hanya jika Anda memahami implikasi dari pengaturan tambahan.',
        ],
        'external_id' => [
            'label' => 'ID Eksternal',
            'helper' => 'Pengidentifikasi unik opsional untuk server ini.',
        ],
        'owner' => [
            'label' => 'Pemilik',
            'helper' => 'Pilih pengguna yang memiliki server ini.',
        ],
        'name' => [
            'label' => 'Nama',
            'placeholder' => 'Nama Server',
            'helper' => 'Nama pendek untuk server ini.',
        ],
        'description' => [
            'label' => 'Keterangan',
            'placeholder' => 'Deskripsi server',
            'helper' => 'Deskripsi opsional untuk server ini.',
        ],
        'node' => [
            'label' => 'simpul',
            'helper' => 'Node tempat server ini akan dikerahkan.',
        ],
        'allocation' => [
            'label' => 'Alokasi Utama',
            'helper' => 'Alokasi IP/port default untuk server ini.',
        ],
        'additional_allocations' => [
            'label' => 'Alokasi Tambahan',
            'helper' => 'Alokasi tambahan opsional untuk ditetapkan.',
        ],
        'nest' => [
            'label' => 'Sarang',
            'helper' => 'Sarang layanan untuk server ini.',
        ],
        'egg' => [
            'label' => 'Telur',
            'helper' => 'Telur yang mendefinisikan perilaku server.',
        ],
        'startup' => [
            'label' => 'Perintah Memulai',
            'helper' => 'Perintah startup untuk server.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'helper' => 'Gambar Docker digunakan untuk menjalankan server ini.',
            'custom' => 'Kebiasaan',
        ],
        'skip_scripts' => [
            'label' => 'Lewati Skrip Telur',
            'helper' => 'Lewati skrip pemasangan telur selama pembuatan.',
        ],
        'start_on_completion' => [
            'label' => 'Mulai saat Selesai',
            'helper' => 'Secara otomatis memulai server setelah instalasi.',
        ],
        'memory' => [
            'label' => 'Ingatan',
            'helper' => 'Alokasi memori total. Setel ke 0 untuk tidak terbatas. (Memori Tidak Terbatas tidak berfungsi untuk Minecraft Eggs karena Perintah Startup)',
        ],
        'swap' => [
            'label' => 'Menukar',
            'helper' => 'Tukar alokasi memori. Setel ke 0 untuk menonaktifkan swap atau -1 untuk mengizinkan pertukaran tanpa batas.',
        ],
        'disk' => [
            'label' => 'Disk',
            'helper' => 'Alokasi ruang disk. Setel ke 0 untuk tidak terbatas.',
        ],
        'io' => [
            'label' => 'IO Berat',
            'helper' => 'Prioritas I/O disk relatif (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Batas CPU dalam persen. 100% berarti satu inti penuh, 200% berarti dua inti penuh, dan seterusnya.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Masukkan ukuran dalam GiB',
            'helper' => 'Anda dapat memasukkan ukuran dalam GiB dengan menggunakan akhiran "GiB" (misalnya 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Utas CPU',
            'helper' => 'Penyematan benang opsional. Contoh: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Nonaktifkan Pembunuh OOM',
            'helper' => 'Cegah kernel menghentikan proses saat kehabisan memori.',
        ],
        'database_limit' => [
            'label' => 'Batas Basis Data',
            'helper' => 'Jumlah maksimum database.',
        ],
        'allocation_limit' => [
            'label' => 'Batas Alokasi',
            'helper' => 'Jumlah alokasi maksimum.',
        ],
        'backup_limit' => [
            'label' => 'Batas Cadangan',
            'helper' => 'Jumlah maksimum cadangan.',
        ],
        'environment' => [
            'key' => 'Variabel',
            'value' => 'Nilai',
            'helper' => 'Variabel lingkungan untuk telur ini.',
        ],
        'use_custom_image' => [
            'label' => 'Gunakan Gambar Kustom',
            'helper' => 'Beralih untuk menggunakan gambar Docker khusus alih-alih gambar yang disediakan oleh telur.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Nama',
        'owner' => 'Pemilik',
        'node' => 'simpul',
        'allocation' => 'Alokasi',
        'status' => 'Status',
        'egg' => 'Telur',
        'memory' => 'Ingatan',
        'disk' => 'Disk',
        'cpu' => 'CPU',
        'created' => 'Dibuat',
        'updated' => 'Diperbarui',
        'installed' => 'Dipasang',
        'no_status' => 'Tidak Ada Status',
        'unlimited' => 'Tak terbatas',
    ],

    'messages' => [
        'created' => 'Server telah berhasil dibuat.',
        'updated' => 'Server telah berhasil diperbarui.',
        'deleted' => 'Server telah berhasil dihapus.',
    ],

    'actions' => [
        'edit' => 'Sunting',
        'random' => 'Acak',
        'toggle_install_status' => 'Alihkan Status Pemasangan',
        'suspend' => 'Menskors',
        'unsuspend' => 'Batalkan penangguhan',
        'suspended' => 'Tergantung',
        'unsuspended' => 'Tidak ditangguhkan',
        'reinstall' => 'Instal ulang',
        'delete' => 'Menghapus',
        'delete_forcibly' => 'Hapus Secara Paksa',
        'view' => 'Melihat',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Anda mencoba menghapus alokasi default untuk server ini tetapi tidak ada alokasi cadangan untuk digunakan.',
        'marked_as_failed' => 'Server ini ditandai telah gagal dalam instalasi sebelumnya. Status saat ini tidak dapat diubah dalam keadaan ini.',
        'bad_variable' => 'Terjadi kesalahan validasi dengan variabel :name.',
        'daemon_exception' => 'Terjadi pengecualian saat mencoba berkomunikasi dengan daemon yang menghasilkan kode respons HTTP/:code. Pengecualian ini telah dicatat. (request id: :request_id)',
        'default_allocation_not_found' => 'Alokasi default yang diminta tidak ditemukan dalam alokasi server ini.',
    ],

    'alerts' => [
        'install_toggled' => 'Status instalasi untuk server ini telah diubah.',
        'server_suspended' => 'Server telah :action.',
        'server_reinstalled' => 'Server ini telah dimasukkan dalam antrian untuk instalasi ulang mulai sekarang.',
        'server_deleted' => 'Server berhasil dihapus dari sistem.',
        'server_delete_failed' => 'Gagal menghapus server.',
        'startup_changed' => 'Konfigurasi startup untuk server ini telah diperbarui. Jika nest atau egg server ini diubah, instal ulang akan terjadi sekarang.',
        'server_created' => 'Server berhasil dibuat di panel. Harap tunggu beberapa menit agar daemon sepenuhnya menginstal server ini.',
        'build_updated' => 'Detail build untuk server ini telah diperbarui. Beberapa perubahan mungkin memerlukan restart agar berlaku.',
        'suspension_toggled' => 'Status penangguhan server telah diubah menjadi :status.',
        'rebuild_on_boot' => 'Server ini telah ditandai memerlukan pembangunan ulang Docker Container. Ini akan terjadi saat server dinyalakan berikutnya.',
        'details_updated' => 'Detail server telah berhasil diperbarui.',
        'docker_image_updated' => 'Berhasil mengubah gambar Docker default yang digunakan untuk server ini. Reboot diperlukan untuk menerapkan perubahan ini.',
        'node_required' => 'Anda harus memiliki setidaknya satu node yang dikonfigurasi sebelum dapat menambahkan server ke panel ini.',
        'transfer_nodes_required' => 'Anda harus memiliki setidaknya dua node yang dikonfigurasi sebelum dapat mentransfer server.',
        'transfer_started' => 'Transfer server telah dimulai.',
        'transfer_not_viable' => 'Node yang Anda pilih tidak memiliki ruang disk atau memori yang cukup untuk menampung server ini.',
        'primary_allocation_updated' => 'Alokasi utama diperbarui.',
        'database_created' => 'Basis data dibuat.',
        'database_password_reset' => 'Reset kata sandi basis data.',
        'database_deleted' => 'Basis data dihapus.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Informasi',
            'build_configuration' => 'Bangun Konfigurasi',
            'startup' => 'Rintisan',
            'manage' => 'Mengelola',
        ],

        'sections' => [
            'resource_management' => 'Manajemen Sumber Daya',
            'application_feature_limits' => 'Batasan Fitur Aplikasi',
            'allocation_management' => 'Manajemen Alokasi',
            'startup_command_modification' => 'Modifikasi Perintah Startup',
            'service_configuration' => 'Konfigurasi Layanan',
            'docker_image_configuration' => 'Konfigurasi Gambar Docker',
            'service_variables' => 'Variabel Layanan',
            'reinstall_server' => 'Instal ulang Server',
            'install_status' => 'Status Pemasangan',
            'suspend_server' => 'Tangguhkan Server',
            'unsuspend_server' => 'Batalkan penangguhan Server',
            'transfer_server' => 'Server Pemindahan',
            'delete_server' => 'Hapus Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Mengubah nilai-nilai ini dapat memicu penginstalan ulang. Server akan segera dihentikan untuk operasi itu.',
            'reinstall_server' => 'Ini akan menginstal ulang server dengan skrip layanan yang ditetapkan. Ini dapat menimpa data server.',
            'install_status' => 'Ubah status instalasi dari uninstall menjadi install, atau sebaliknya.',
            'suspend_server' => 'Ini akan menghentikan proses yang berjalan dan memblokir pengguna dari mengelola server melalui panel atau API.',
            'unsuspend_server' => 'Ini akan membatalkan penangguhan server dan memulihkan akses pengguna normal.',
            'transfer_server_transferring' => 'Server ini sedang ditransfer ke node lain.',
            'transfer_server' => 'Transfer server ini ke node lain yang terhubung ke panel ini.',
            'delete_server' => 'Ini secara permanen menghapus server dari panel dan Agen. Penghapusan paksa melewatkan penghapusan Agen jika perlu.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Nama Server',
                'helper' => 'Batas karakter: a-zA-Z0-9_-, spasi, dan karakter standar yang dapat dicetak.',
            ],
            'server_owner' => [
                'label' => 'Pemilik Server',
                'helper' => 'Mengubah kepemilikan secara otomatis mencabut token daemon untuk pemilik sebelumnya.',
            ],
            'server_description' => [
                'label' => 'Deskripsi Server',
                'helper' => 'Penjelasan singkat tentang server ini.',
            ],
            'server_uuid' => [
                'label' => 'UUID server',
            ],
            'server_uuid_short' => [
                'label' => 'UUID Server (Pendek)',
            ],
            'external_identifier' => [
                'label' => 'Pengenal Eksternal',
                'helper' => 'Biarkan kosong agar tidak menetapkan pengenal eksternal. ID eksternal harus unik untuk server ini.',
            ],
            'game_port' => [
                'label' => 'Pelabuhan Permainan',
                'helper' => 'Alamat koneksi default yang akan digunakan untuk server game ini.',
            ],
            'additional_ports' => [
                'label' => 'Port Tambahan',
                'helper' => 'Tetapkan atau hapus port tambahan. Port identik pada IP berbeda tidak dapat ditetapkan ke server yang sama.',
            ],
            'startup_command' => [
                'label' => 'Perintah Memulai',
                'helper' => 'Tersedia secara default: {{SERVER_MEMORY}}, {{SERVER_IP}}, dan {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Perintah Startup Default',
                'error' => 'KESALAHAN: Startup Tidak Ditentukan!',
            ],
            'cpu_limit' => [
                'label' => 'Batas CPU',
                'helper' => 'Setiap inti virtual adalah 100%. Setel 0 untuk waktu CPU yang tidak dibatasi.',
            ],
            'cpu_pinning' => [
                'label' => 'Penyematan CPU',
                'helper' => 'Lanjutan: biarkan kosong untuk semua inti. Contoh: 0, 0-1,3, atau 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Memori yang Dialokasikan',
                'helper' => 'Jumlah memori maksimum yang diperbolehkan untuk penampung ini. Tetapkan 0 untuk tidak terbatas.',
            ],
            'allocated_swap' => [
                'label' => 'Tukar yang Dialokasikan',
                'helper' => 'Setel 0 untuk menonaktifkan pertukaran, atau -1 untuk mengizinkan pertukaran tanpa batas.',
            ],
            'disk_space_limit' => [
                'label' => 'Batas Ruang Disk',
                'helper' => 'Setel 0 untuk mengizinkan penggunaan disk tanpa batas.',
            ],
            'block_io_proportion' => [
                'label' => 'Blokir Proporsi IO',
                'helper' => 'Lanjutan: Performa IO relatif terhadap container lain yang sedang berjalan. Nilainya harus 10 hingga 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Nonaktifkan Pembunuh OOM',
                'helper' => 'Mengaktifkan OOM killer dapat menyebabkan proses server keluar secara tidak terduga.',
            ],
            'database_limit' => [
                'label' => 'Batas Basis Data',
                'helper' => 'Jumlah total database yang boleh dibuat oleh pengguna untuk server ini.',
            ],
            'allocation_limit' => [
                'label' => 'Batas Alokasi',
                'helper' => 'Jumlah total alokasi yang boleh dibuat oleh pengguna untuk server ini.',
            ],
            'backup_limit' => [
                'label' => 'Batas Cadangan',
                'helper' => 'Jumlah total cadangan yang dapat dibuat untuk server ini.',
            ],
            'image' => [
                'label' => 'Gambar',
                'helper' => 'Pilih gambar dari dropdown, atau masukkan gambar khusus di bawah.',
            ],
            'custom_image' => [
                'label' => 'Gambar Kustom',
                'placeholder' => 'Atau masukkan gambar khusus...',
                'helper' => 'Ini adalah image Docker yang akan digunakan untuk menjalankan server ini.',
            ],
            'transfer_node' => [
                'label' => 'simpul',
                'helper' => 'Node tempat server ini akan ditransfer.',
            ],
            'transfer_allocation' => [
                'label' => 'Alokasi Default',
                'helper' => 'Alokasi utama yang akan ditetapkan ke server ini.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Alokasi Tambahan',
                'helper' => 'Alokasi tambahan untuk ditetapkan ke server ini pada saat transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Instal ulang Server',
            'toggle_install_status' => 'Alihkan Status Pemasangan',
            'suspend_server' => 'Tangguhkan Server',
            'unsuspend_server' => 'Batalkan penangguhan Server',
            'transfer_server' => 'Server Pemindahan',
            'confirm' => 'Mengonfirmasi',
            'delete_server' => 'Hapus Server',
            'forcibly_delete_server' => 'Hapus Server Secara Paksa',
        ],
    ],

    'allocations' => [
        'title' => 'Alokasi',

        'table' => [
            'ip' => 'AKU P',
            'port' => 'Pelabuhan',
            'alias' => 'Alias',
            'primary' => 'Utama',
            'notes' => 'Catatan',
            'created' => 'Dibuat',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Tidak Ada Alias ​​yang Ditugaskan',
        ],

        'actions' => [
            'make_primary' => 'Jadikan Pratama',
        ],
    ],

    'databases' => [
        'title' => 'Basis Data',

        'table' => [
            'database' => 'Basis data',
            'username' => 'Nama belakang',
            'remote' => 'Terpencil',
            'host' => 'Tuan rumah',
            'max_connections' => 'Koneksi Maks',
            'created' => 'Dibuat',
        ],

        'placeholder' => [
            'unlimited' => 'Tak terbatas',
        ],

        'actions' => [
            'create_database' => 'Buat Basis Data',
            'reset_password' => 'Atur Ulang Kata Sandi',
            'delete' => 'Menghapus',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Nama Basis Data',
                'helper' => 'Panel akan mengawalinya dengan ID server, cocok dengan panel admin lama.',
            ],
            'database_host' => [
                'label' => 'Tuan Rumah Basis Data',
            ],
            'remote' => [
                'label' => 'Terpencil',
            ],
            'max_connections' => [
                'label' => 'Koneksi Maks',
            ],
        ],
    ],
];
