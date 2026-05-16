<?php

return [
    'title' => 'Kullanıcı',
    'exceptions' => [
        'delete_self' => 'Kendi hesabınızı silemezsiniz.',
        'user_has_servers' => 'Hesabına bağlı aktif sunucuları olan bir kullanıcıyı silemezsiniz. Lütfen devam etmeden önce sunucularını silin.',
    ],
    'notices' => [
        'account_created' => 'Hesap başarıyla oluşturuldu.',
        'account_updated' => 'Hesap başarıyla güncellendi.',
    ],
    'details' => [
        'account_details' => 'Hesap Detayları',
        'external_id' => 'Harici Kimlik',
        'username' => 'Kullanıcı Adı',
        'email' => 'E-posta Adresi',
        'first_name' => 'Ad',
        'last_name' => 'Soyad',
        'language' => 'Dil',
        'geolocate' => 'Coğrafi Konum Belirleme (Otomatik)',
        'password' => 'Parola',
        'password_confirmation' => 'Parolayı Onayla',
        'root_admin' => 'Kök Yönetici (Root)',
        'root_admin_desc' => 'Bu kullanıcı, sistemdeki tüm sunuculara ve ayarlara tam erişime sahip olacaktır.',
        'privileges' => 'Ayrıcalıklar',
        'admin_status' => 'Yönetici Durumu',
    ],
];
