<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Pengguna Sistem',
        'system' => 'Sistem',
        'using-api-key' => 'Menggunakan Kunci API',
        'using-sftp' => 'Menggunakan SFTP',
    ],
    'auth' => [
        'fail' => 'Gagal masuk',
        'success' => 'Berhasil masuk',
        'password-reset' => 'Kata sandi diatur ulang',
        'reset-password' => 'Meminta pengaturan ulang kata sandi',
        'checkpoint' => 'Otentikasi dua faktor diminta',
        'recovery-token' => 'Menggunakan token pemulihan dua faktor',
        'token' => 'Menyelesaikan tantangan dua faktor',
        'ip-blocked' => 'Permintaan diblokir dari alamat IP yang tidak terdaftar untuk :identifier',
        'sftp' => [
            'fail' => 'Gagal masuk SFTP',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'Mengubah email dari :old menjadi :new',
            'password-changed' => 'Mengubah kata sandi',
            'language-changed' => 'Mengubah bahasa dari :old menjadi :new',
        ],
        'api-key' => [
            'create' => 'Membuat kunci API baru :identifier',
            'delete' => 'Menghapus kunci API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Menambahkan kunci SSH :fingerprint ke akun',
            'delete' => 'Menghapus kunci SSH :fingerprint dari akun',
        ],
        'two-factor' => [
            'create' => 'Mengaktifkan otentikasi dua faktor',
            'delete' => 'Menonaktifkan otentikasi dua faktor',
        ],
    ],
    'server' => [
        'reinstall' => 'Menginstal ulang server',
        'console' => [
            'command' => 'Menjalankan ":command" pada server',
        ],
        'power' => [
            'start' => 'Menyalakan server',
            'stop' => 'Mematikan server',
            'restart' => 'Merestart server',
            'kill' => 'Mematikan paksa proses server',
        ],
        'backup' => [
            'download' => 'Mengunduh backup :name',
            'delete' => 'Menghapus backup :name',
            'restore' => 'Memulihkan backup :name (file yang dihapus: :truncate)',
            'restore-complete' => 'Menyelesaikan pemulihan backup :name',
            'restore-failed' => 'Gagal menyelesaikan pemulihan backup :name',
            'start' => 'Memulai backup baru :name',
            'complete' => 'Menandai backup :name sebagai selesai',
            'fail' => 'Menandai backup :name sebagai gagal',
            'lock' => 'Mengunci backup :name',
            'unlock' => 'Membuka kunci backup :name',
        ],
        'database' => [
            'create' => 'Membuat database baru :name',
            'rotate-password' => 'Kata sandi dirotasi untuk database :name',
            'delete' => 'Menghapus database :name',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => 'Mengompres :count file di :directory',
            'read' => 'Melihat isi dari :file',
            'copy' => 'Membuat salinan dari :file',
            'create-directory' => 'Membuat direktori :directory:name',
            'decompress' => 'Mengekstrak :files di :directory',
            'delete_one' => 'Menghapus :directory:files.0',
            'delete_other' => 'Menghapus :count file di :directory',
            'download' => 'Mengunduh :file',
            'pull' => 'Mengunduh file remote dari :url ke :directory',
            'rename_one' => 'Mengubah nama :directory:files.0.from menjadi :directory:files.0.to',
            'rename_other' => 'Mengubah nama :count file di :directory',
            'write' => 'Menulis konten baru ke :file',
            'upload' => 'Memulai unggah file',
            'uploaded' => 'Mengunggah :directory:file',
        ],
        'sftp' => [
            'denied' => 'Akses SFTP diblokir karena izin',
            'create_one' => 'Membuat :files.0',
            'create_other' => 'Membuat :count file baru',
            'write_one' => 'Memodifikasi isi dari :files.0',
            'write_other' => 'Memodifikasi isi dari :count file',
            'delete_one' => 'Menghapus :files.0',
            'delete_other' => 'Menghapus :count file',
            'create-directory_one' => 'Membuat direktori :files.0',
            'create-directory_other' => 'Membuat :count direktori',
            'rename_one' => 'Mengubah nama :files.0.from menjadi :files.0.to',
            'rename_other' => 'Mengubah nama atau memindahkan :count file',
        ],
        'allocation' => [
            'create' => 'Menambahkan :allocation ke server',
            'notes' => 'Memperbarui catatan untuk :allocation dari ":old" menjadi ":new"',
            'primary' => 'Menetapkan :allocation sebagai alokasi server utama',
            'delete' => 'Menghapus alokasi :allocation',
        ],
        'schedule' => [
            'create' => 'Membuat jadwal :name',
            'update' => 'Memperbarui jadwal :name',
            'execute' => 'Menjalankan jadwal :name secara manual',
            'delete' => 'Menghapus jadwal :name',
        ],
        'task' => [
            'create' => 'Membuat tugas ":action" baru untuk jadwal :name',
            'update' => 'Memperbarui tugas ":action" untuk jadwal :name',
            'delete' => 'Menghapus tugas untuk jadwal :name',
        ],
        'settings' => [
            'rename' => 'Mengubah nama server dari :old menjadi :new',
            'description' => 'Mengubah deskripsi server dari :old menjadi :new',
        ],
        'startup' => [
            'edit' => 'Mengubah variabel :variable dari ":old" menjadi ":new"',
            'image' => 'Memperbarui Docker Image untuk server dari :old menjadi :new',
        ],
        'subuser' => [
            'create' => 'Menambahkan :email sebagai subpengguna',
            'update' => 'Memperbarui izin subpengguna untuk :email',
            'delete' => 'Menghapus :email sebagai subpengguna',
        ],
    ],
];
