<?php

return [
    'label' => 'Rol',
    'plural-label' => 'Roller',
    'all' => 'Tümü',
    'select_all' => 'Tümünü seç',
    'deselect_all' => 'Tüm seçimi kaldır',
    'details' => [
        'title' => 'Rol Ayrıntıları',
        'description' => 'Roller, bir kullanıcının panelde, yönetim alanında ve kendisine ait olmayan sunucularda neler yapabileceğini belirler.',
        'name' => 'Ad',
        'name_placeholder' => 'Destek Ekibi',
        'description_label' => 'Açıklama',
        'description_placeholder' => 'Bu rolün ne için olduğu.',
    ],
    'table' => [
        'name' => 'Ad',
        'permissions' => 'İzinler',
        'users' => 'Kullanıcılar',
        'updated' => 'Son Güncelleme',
    ],
    'groups' => [
        'management' => [
            'title' => 'Kaynak Yönetimi',
            'description' => 'Sunucular, onları kullanan kişiler ve üzerinde çalıştıkları altyapı.',
        ],
        'service' => [
            'title' => 'Hizmet Yönetimi',
            'description' => 'Bir sunucuya nelerin kurulabileceği ve panelin kendisinin nasıl genişletilip güncellendiği.',
        ],
        'administration' => [
            'title' => 'Yönetim',
            'description' => 'Panel genelinde yapılandırma, görünüm ve içgörüler.',
        ],
    ],
    'resources' => [
        'servers' => 'Sunucular',
        'users' => 'Kullanıcılar',
        'roles' => 'Roller',
        'nodes' => 'Düğümler',
        'locations' => 'Konumlar',
        'database_hosts' => 'Veritabanı Sunucuları',
        'nests' => 'Nestler',
        'eggs' => 'Egg\'ler',
        'mounts' => 'Bağlama Noktaları',
        'alerts' => 'Uyarılar',
        'webhooks' => 'Webhook\'lar',
        'extensions' => 'Uzantılar',
        'activity_log' => 'Etkinlik Günlüğü',
        'monitoring' => 'İzleme',
        'settings' => 'Ayarlar',
        'designify' => 'Designify',
        'updates' => 'Yazılım Güncellemeleri',
    ],
    'actions' => [
        'send_email' => 'E-posta gönder',
        'read' => 'Görüntüle',
        'create' => 'Oluştur',
        'update' => 'Düzenle',
        'delete' => 'Sil',
        'assign' => 'Kullanıcılara ata',
        'manage' => 'Yönet',
        'install' => 'Güncellemeleri yükle',
    ],
    'messages' => [
        'in_use' => 'Bu rol hâlâ kullanıcılara atanmış. Silmeden önce onlardan kaldırın.',
    ],
    'user' => [
        'roles' => 'Roller',
        'roles_self' => 'Kendi rollerinizi değiştiremezsiniz.',
        'none' => 'Yok',
    ],
];
