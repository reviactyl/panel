<?php

return [
    'username-required' => 'Bir kullanıcı adı veya e-posta adresi gereklidir.',
    'password-required' => 'Lütfen hesap parolanızı girin.',
    'email-required' => 'Devam etmek için geçerli bir e-posta adresi gereklidir.',

    'login-title' => 'Devam Etmek İçin Giriş Yapın',

    'username-label' => 'Kullanıcı Adı veya E-posta',
    'password-label' => 'Parola',

    'login-button' => 'Giriş Yap',
    'return' => 'Giriş Ekranına Dön',

    'social' => [
        'or' => 'VEYA',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'Bu hesap herhangi bir :provider hesabına bağlı değil. Lütfen önce e-posta ve parolanızla giriş yapın, ardından Hesap Ayarları sayfasından :provider hesabınızı bağlayın.',
    ],

    'forgot-password' => [
        'title' => 'Parola Sıfırlama İsteği',
        'label' => 'Parolamı Unuttum?',
        'email-label' => 'E-posta',
        'email-content' => 'Parolanızı sıfırlama talimatlarını almak için hesap e-posta adresinizi girin.',
        'send-email' => 'E-posta Gönder',
    ],

    'checkpoint' => [
        'title' => 'Cihaz Kontrol Noktası',
        'recovery-code' => 'Kurtarma Kodu',
        'auth-code' => 'Doğrulama Kodu',
        'is-missing' => 'Devam etmek için bu hesaba 2 adımlı doğrulamayı kurarken oluşturulan kurtarma kodlarından birini girin.',
        'is-not-missing' => 'Cihazınız tarafından oluşturulan iki aşamalı doğrulama jetonunu girin.',
        'button' => 'Devam Et',
        'lost-device' => 'Cihazımı Kaybettim',
        'not-lost-device' => 'Cihazım Yanımda',

    ],

    'reset-password' => [
        'new-required' => 'Yeni bir parola gereklidir.',
        'min-required' => 'Yeni parolanız en az 8 karakter uzunluğunda olmalıdır.',
        'no-match' => 'Yeni parolanız eşleşmiyor.',
        'email-label' => 'E-posta',
        'new-label' => 'Yeni Parola',
        'min-length' => 'Parolalar en az 8 karakter uzunluğunda olmalıdır.',
        'confirm-label' => 'Yeni Parolayı Onayla',
        'label' => 'Parolayı Sıfırla',
    ],

    'register' => [
        'no-match' => 'Parolanız eşleşmiyor.',
        'namefirst-label' => 'Ad',
        'namelast-label' => 'Soyad',
        'email-label' => 'E-posta',
        'username-label' => 'Kullanıcı Adı',
        'password-label' => 'Parola',
        'min-length' => 'Parolalar en az 8 karakter uzunluğunda olmalıdır.',
        'confirm-label' => 'Parolayı Onayla',
        'label' => 'Kayıt Ol',
        'create-account' => 'Create Account',
    ],

    'failed' => 'Bu kimlik bilgileriyle eşleşen bir hesap bulunamadı.',

    'two_factor' => [
        'label' => '2 Adımlı Doğrulama Jetonu',
        'label_help' => 'Bu hesap, devam etmek için ikinci bir doğrulama katmanı gerektiriyor. Girişi tamamlamak için lütfen cihazınız tarafından oluşturulan kodu girin.',
        'checkpoint_failed' => 'İki aşamalı doğrulama jetonu geçersiz.',
    ],

    'throttle' => 'Çok fazla giriş denemesi. Lütfen :seconds saniye sonra tekrar deneyin.',
    'password_requirements' => 'Parola en az 8 karakter uzunluğunda olmalı ve bu siteye özel olmalıdır.',
    '2fa_must_be_enabled' => 'Yönetici, Paneli kullanabilmeniz için hesabınızda 2 Adımlı Doğrulamanın etkinleştirilmesini zorunlu kıldı.',
];
