<?php

return [
    'title' => 'Pengguna',
    'exceptions' => [
        'delete_self' => 'Anda tidak dapat menghapus akun Anda sendiri.',
        'user_has_servers' => 'Tidak dapat menghapus pengguna dengan server aktif yang terhubung ke akun mereka. Harap hapus server mereka sebelum melanjutkan.',
    ],
    'notices' => [
        'account_created' => 'Akun telah berhasil dibuat.',
        'account_updated' => 'Akun telah berhasil diperbarui.',
    ],
    'details' => [
        'account_details' => 'Detail Akun',
        'external_id' => 'ID Eksternal',
        'username' => 'Nama belakang',
        'email' => 'Alamat Email',
        'first_name' => 'Nama Depan',
        'last_name' => 'Nama Belakang',
        'language' => 'Bahasa',
        'geolocate' => 'Geolokasi (Otomatis)',
        'password' => 'Kata Sandi',
        'password_confirmation' => 'Konfirmasi Kata Sandi',
        'root_admin' => 'Administrator',
        'root_admin_desc' => 'Pengguna ini akan memiliki akses penuh ke semua server dan pengaturan pada sistem.',
        'privileges' => 'Hak Akses',
        'admin_status' => 'Status Admin',
    ],
];
