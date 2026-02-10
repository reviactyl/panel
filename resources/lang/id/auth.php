<?php

return [
    'username-required' => 'Nama pengguna atau email wajib diisi.',
    'password-required' => 'Silakan masukkan kata sandi akun Anda.',
    'email-required' => 'Alamat email yang valid wajib diisi untuk melanjutkan.',

    'login-title' => 'Masuk untuk Melanjutkan',

    'username-label' => 'Nama Pengguna atau Email',
    'password-label' => 'Kata Sandi',

    'login-button' => 'Masuk',
    'return' => 'Kembali ke Login',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'This account has not been linked to any :provider account. Please log in with your email and password first, then link your :provider account in the Account Settings page.',
    ],

    'forgot-password' => [
        'title' => 'Minta Atur Ulang Kata Sandi',
        'label' => 'Lupa Kata Sandi?',
        'email-label' => 'Email',
        'email-content' => 'Masukkan alamat email akun Anda untuk menerima instruksi pengaturan ulang kata sandi.',
        'send-email' => 'Kirim Email',
    ],

    'checkpoint' => [
        'title' => 'Pemeriksaan Perangkat',
        'recovery-code' => 'Kode Pemulihan',
        'auth-code' => 'Kode Otentikasi',
        'is-missing' => 'Masukkan salah satu kode pemulihan yang dibuat saat Anda mengatur otentikasi 2-faktor pada akun ini untuk melanjutkan.',
        'is-not-missing' => 'Masukkan token 2-faktor yang dibuat oleh perangkat Anda.',
        'button' => 'Lanjutkan',
        'lost-device' => 'Saya Kehilangan Perangkat Saya',
        'not-lost-device' => 'Saya Memiliki Perangkat Saya',

    ],

    'reset-password' => [
        'new-required' => 'Kata sandi baru wajib diisi.',
        'min-required' => 'Kata sandi baru Anda harus memiliki setidaknya 8 karakter.',
        'no-match' => 'Kata sandi baru Anda tidak cocok.',
        'email-label' => 'Email',
        'new-label' => 'Kata Sandi Baru',
        'min-length' => 'Kata sandi harus memiliki setidaknya 8 karakter.',
        'confirm-label' => 'Konfirmasi Kata Sandi Baru',
        'label' => 'Atur Ulang Kata Sandi',
    ],

    'register' => [
        'no-match' => 'Your password does not match.',
        'namefirst-label' => 'First Name',
        'namelast-label' => 'Last Name',
        'email-label' => 'Email',
        'username-label' => 'UserName',
        'password-label' => 'Password',
        'min-length' => 'Passwords must be at least 8 characters in length.',
        'confirm-label' => 'Confirm Password',
        'label' => 'Register',
    ],
    
    'failed' => 'Tidak ada akun yang cocok dengan kredensial tersebut.',

    'two_factor' => [
        'label' => 'Token 2-Faktor',
        'label_help' => 'Akun ini memerlukan lapisan otentikasi kedua untuk melanjutkan. Silakan masukkan kode yang dibuat oleh perangkat Anda untuk menyelesaikan login ini.',
        'checkpoint_failed' => 'Token otentikasi dua faktor tidak valid.',
    ],

    'throttle' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.',
    'password_requirements' => 'Kata sandi harus memiliki setidaknya 8 karakter dan harus unik untuk situs ini.',
    '2fa_must_be_enabled' => 'Administrator mengharuskan Otentikasi 2-Faktor diaktifkan pada akun Anda untuk menggunakan Panel.',
];
