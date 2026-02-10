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
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'This account has not been linked to any :provider account. Please log in with your email and password first, then link your :provider account in the Account Settings page.',
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
