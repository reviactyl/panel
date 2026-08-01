<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Sistem Kullanıcısı',
        'system' => 'Sistem',
        'using-api-key' => 'API Anahtarı Kullanılıyor',
        'using-sftp' => 'SFTP Kullanılıyor',
    ],
    'auth' => [
        'fail' => 'Giriş başarısız',
        'success' => 'Giriş yapıldı',
        'password-reset' => 'Parola sıfırlandı',
        'reset-password' => 'Parola sıfırlama isteği gönderildi',
        'checkpoint' => 'İki aşamalı doğrulama istendi',
        'recovery-token' => 'İki aşamalı kurtarma jetonu kullanıldı',
        'token' => 'İki aşamalı doğrulama tamamlandı',
        'ip-blocked' => ':identifier için listelenmemiş IP adresinden gelen istek engellendi',
        'sftp' => [
            'fail' => 'SFTP girişi başarısız',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'E-posta adresi :old adresinden :new adresine değiştirildi',
            'password-changed' => 'Parola değiştirildi',
            'language-changed' => 'Dil :old dilinden :new diline değiştirildi',
        ],
        'api-key' => [
            'create' => 'Yeni API anahtarı :identifier oluşturuldu',
            'delete' => 'API anahtarı :identifier silindi',
        ],
        'ssh-key' => [
            'create' => 'Hesaba SSH anahtarı :fingerprint eklendi',
            'delete' => 'Hesaptan SSH anahtarı :fingerprint kaldırıldı',
        ],
        'two-factor' => [
            'create' => 'İki aşamalı doğrulama etkinleştirildi',
            'delete' => 'İki aşamalı doğrulama devre dışı bırakıldı',
        ],
    ],
    'server' => [
        'reinstall' => 'Sunucu yeniden kuruldu',
        'console' => [
            'command' => 'Sunucuda ":command" komutu çalıştırıldı',
        ],
        'power' => [
            'start' => 'Sunucu başlatıldı',
            'stop' => 'Sunucu durduruldu',
            'restart' => 'Sunucu yeniden başlatıldı',
            'kill' => 'Sunucu işlemi sonlandırıldı',
        ],
        'backup' => [
            'download' => ':name yedeği indirildi',
            'delete' => ':name yedeği silindi',
            'restore' => ':name yedeği geri yüklendi (silinen dosyalar: :truncate)',
            'restore-complete' => ':name yedeğinin geri yüklenmesi tamamlandı',
            'restore-failed' => ':name yedeğinin geri yüklenmesi tamamlanamadı',
            'start' => 'Yeni bir :name yedeği başlatıldı',
            'complete' => ':name yedeği tamamlandı olarak işaretlendi',
            'fail' => ':name yedeği başarısız olarak işaretlendi',
            'lock' => ':name yedeği kilitlendi',
            'unlock' => ':name yedeğinin kilidi açıldı',
        ],
        'database' => [
            'create' => 'Yeni veritabanı :name oluşturuldu',
            'rotate-password' => ':name veritabanı için parola yenilendi',
            'delete' => ':name veritabanı silindi',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => ':directory dizinindeki :count dosya sıkıştırıldı',
            'read' => ':file içeriği görüntülendi',
            'copy' => ':file kopyası oluşturuldu',
            'create-directory' => ':directory:name dizini oluşturuldu',
            'decompress' => ':directory dizinindeki :files dosyaları açıldı',
            'delete_one' => ':directory:files.0 silindi',
            'delete_other' => ':directory dizinindeki :count dosya silindi',
            'download' => ':file indirildi',
            'pull' => ':url adresinden :directory dizinine uzak dosya indirildi',
            'rename_one' => ':directory:files.0.from dosyası :directory:files.0.to olarak yeniden adlandırıldı',
            'rename_other' => ':directory dizinindeki :count dosya yeniden adlandırıldı',
            'write' => ':file dosyasına yeni içerik yazıldı',
            'upload' => 'Dosya yüklemesi başlatıldı',
            'uploaded' => ':directory:file yüklendi',
        ],
        'sftp' => [
            'denied' => 'İzinler nedeniyle SFTP erişimi engellendi',
            'create_one' => ':files.0 oluşturuldu',
            'create_other' => ':count yeni dosya oluşturuldu',
            'write_one' => ':files.0 içeriği değiştirildi',
            'write_other' => ':count dosyanın içeriği değiştirildi',
            'delete_one' => ':files.0 silindi',
            'delete_other' => ':count dosya silindi',
            'create-directory_one' => ':files.0 dizini oluşturuldu',
            'create-directory_other' => ':count dizin oluşturuldu',
            'rename_one' => ':files.0.from dosyası :files.0.to olarak yeniden adlandırıldı',
            'rename_other' => ':count dosya yeniden adlandırıldı veya taşındı',
        ],
        'allocation' => [
            'create' => 'Sunucuya :allocation eklendi',
            'notes' => ':allocation notları ":old" değerinden ":new" değerine güncellendi',
            'primary' => ':allocation birincil sunucu tahsisi olarak ayarlandı',
            'delete' => ':allocation tahsisi silindi',
        ],
        'schedule' => [
            'create' => ':name zamanlaması oluşturuldu',
            'update' => ':name zamanlaması güncellendi',
            'execute' => ':name zamanlaması elle çalıştırıldı',
            'delete' => ':name zamanlaması silindi',
        ],
        'task' => [
            'create' => ':name zamanlaması için yeni ":action" görevi oluşturuldu',
            'update' => ':name zamanlaması için ":action" görevi güncellendi',
            'delete' => ':name zamanlaması için bir görev silindi',
        ],
        'settings' => [
            'rename' => 'Sunucu adı :old iken :new olarak değiştirildi',
            'description' => 'Sunucu açıklaması :old iken :new olarak değiştirildi',
        ],
        'startup' => [
            'edit' => ':variable değişkeni ":old" iken ":new" olarak değiştirildi',
            'image' => 'Sunucu için Docker Görüntüsü :old iken :new olarak güncellendi',
        ],
        'subuser' => [
            'create' => ':email alt kullanıcı olarak eklendi',
            'update' => ':email için alt kullanıcı izinleri güncellendi',
            'delete' => ':email alt kullanıcısı kaldırıldı',
        ],
    ],
];
