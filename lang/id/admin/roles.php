<?php

return [
    'label' => 'Peran',
    'plural-label' => 'Peran',
    'all' => 'Semua',
    'select_all' => 'Pilih semua',
    'deselect_all' => 'Batalkan semua pilihan',
    'details' => [
        'title' => 'Detail Peran',
        'description' => 'Peran menentukan apa yang dapat dilakukan pengguna di panel, di area admin dan pada server yang bukan miliknya.',
        'name' => 'Nama',
        'name_placeholder' => 'Tim Dukungan',
        'description_label' => 'Deskripsi',
        'description_placeholder' => 'Untuk apa peran ini.',
    ],
    'table' => [
        'name' => 'Nama',
        'permissions' => 'Izin',
        'users' => 'Pengguna',
        'updated' => 'Terakhir Diperbarui',
    ],
    'groups' => [
        'management' => [
            'title' => 'Manajemen',
            'description' => 'Server, orang yang menggunakannya, dan infrastruktur tempat server berjalan.',
        ],
        'service' => [
            'title' => 'Manajemen Layanan',
            'description' => 'Apa yang dapat dipasang di server serta bagaimana panel diperluas dan diperbarui.',
        ],
        'administration' => [
            'title' => 'Administrasi',
            'description' => 'Konfigurasi, tampilan, dan wawasan untuk seluruh panel.',
        ],
    ],
    'resources' => [
        'servers' => 'Server',
        'users' => 'Pengguna',
        'roles' => 'Peran',
        'nodes' => 'Node',
        'locations' => 'Lokasi',
        'database_hosts' => 'Host Database',
        'nests' => 'Nest',
        'eggs' => 'Egg',
        'mounts' => 'Mount',
        'alerts' => 'Peringatan',
        'webhooks' => 'Webhook',
        'extensions' => 'Ekstensi',
        'activity_log' => 'Catatan Aktivitas',
        'monitoring' => 'Pemantauan',
        'settings' => 'Pengaturan',
        'designify' => 'Designify',
        'updates' => 'Pembaruan Perangkat Lunak',
    ],
    'actions' => [
        'send_email' => 'Kirim email',
        'read' => 'Lihat',
        'create' => 'Buat',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'assign' => 'Tetapkan ke pengguna',
        'manage' => 'Kelola',
        'install' => 'Pasang pembaruan',
    ],
    'messages' => [
        'in_use' => 'Peran ini masih ditetapkan ke pengguna. Lepaskan dari mereka sebelum menghapusnya.',
    ],
    'user' => [
        'roles' => 'Peran',
        'roles_self' => 'Anda tidak dapat mengubah peran Anda sendiri.',
        'none' => 'Tidak ada',
    ],
];
