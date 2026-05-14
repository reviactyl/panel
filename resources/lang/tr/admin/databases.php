<?php

return [

    'label' => 'Veritabanı',
    'plural-label' => 'Veritabanları',

    'none' => 'Yok',

    'sections' => [
        'host_details' => [
            'title' => 'Sunucu Detayları',
            'description' => 'Veritabanı sunucusu bağlantı ayarlarını yapılandırın.',
        ],

        'authentication' => [
            'title' => 'Kimlik Doğrulama',
        ],

        'linked_node' => [
            'title' => 'Bağlı Düğüm',
        ],
    ],

    'placeholders' => [
        'name' => 'Üretim MySQL\'i',
    ],

    'helpers' => [
        'host' => 'Veritabanı sunucusunun ana bilgisayar adı veya IP adresi.',
        'linked_node' => 'İsteğe bağlı. Bu sunucuyu belirli bir düğüme bağlayın.',
    ],

    'fields' => [
        'linked_node' => 'Bağlı Düğüm',
    ],

    'columns' => [
        'id' => 'KİMLİK',
        'name' => 'İsim',
        'host' => 'Ev sahibi',
        'port' => 'Liman',
        'username' => 'Kullanıcı Adı',
        'linked_node' => 'Bağlı Düğüm',
        'databases' => 'Veritabanları',
        'created' => 'Oluşturuldu',
    ],

    'actions' => [
        'edit' => 'Düzenle',
        'delete' => 'Sil',
    ],

    'errors' => [
        'cannot_delete' => 'İlişkili veritabanlarına sahip bir veritabanı sunucusu silinemez.',
    ],

];
