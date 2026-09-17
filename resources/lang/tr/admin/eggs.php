<?php

return [

    'tabs' => [
        'configuration' => 'Yumurta Yapılandırması',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Yapılandırma',
        ],
        'identity' => [
            'title' => 'Kimlik',
        ],
        'docker_images' => [
            'title' => 'Docker Görselleri',
            'description' => 'Bu yumurtayı kullanan sunucuların kullanabileceği liman işçisi görüntüleri. Her satıra bir tane girin.',
        ],
        'process_management' => [
            'title' => 'Süreç Yönetimi',
        ],
        'variables' => [
            'title' => 'Değişkenler',
        ],
        'install_script' => [
            'title' => 'Komut Dosyasını Yükle',
        ],
    ],

    'fields' => [
        'nest' => 'Yuva',
        'uuid' => 'UUID',
        'name' => 'İsim',
        'author' => 'Yazar',
        'image' => 'Resim',
        'description' => 'Tanım',
        'image_name' => 'Resim Adı',
        'image_uri' => 'Resim URI\'sı',
        'add_docker_image' => 'Docker Görüntüsü Ekle',
        'force_outgoing_ip' => 'Giden IP\'yi Zorla',
        'features' => 'Özellikler',
        'startup' => 'Başlatma Komutu',
        'config_stop' => 'Durdurma Komutu',
        'config_from' => 'Ayarları Şuradan Kopyala:',
        'config_startup' => 'Yapılandırmayı Başlat (JSON)',
        'config_logs' => 'Günlük Yapılandırması (JSON)',
        'config_files' => 'Yapılandırma Dosyaları (JSON)',
        'file_denylist' => 'Dosya Red Listesi',
        'env_variable' => 'Ortam Değişkeni',
        'user_viewable' => 'Kullanıcılar Görüntüleyebilir',
        'user_editable' => 'Kullanıcılar Düzenleyebilir',
        'rules' => 'Giriş Kuralları',
        'default_value' => 'Varsayılan Değer',
        'script_install' => 'Komut Dosyasını Yükle',
        'script_container' => 'Komut Dosyası Kabı',
        'script_entry' => 'Komut Dosyası Giriş Noktası Komutu',
        'copy_script_from' => 'Komut Dosyasını Şuradan Kopyala:',
        'script_is_privileged' => 'Ayrıcalıklı',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Tüm giden ağ trafiğinin Kaynak IP\'sinin, sunucunun birincil tahsis IP\'sinin IP\'sine NAT\'lenmesine zorlar.',
        'features' => 'Yumurtaya ait ek özellikler. Ek panel değişikliklerini yapılandırmak için kullanışlıdır.',
        'file_denylist' => 'Kullanıcı tarafından düzenlenmemesi gereken dosyalar.',
        'script_is_privileged' => 'Kurulum betiğini ayrıcalıklı bir kapsayıcı (kök) olarak çalıştırın.',
    ],

    'actions' => [
        'export' => 'İhracat',
        'create' => 'Yumurta Oluştur',
        'edit' => 'Düzenlemek',
    ],

    'notices' => [
        'cannot_delete' => 'Yumurta silinemiyor',
        'cannot_delete_body' => 'Bu yumurtanın ilişkili :count sunucuları var. Lütfen önce bunları silin veya yeniden atayın.',
        'cannot_delete_multiple' => 'Yumurtalar sunucularla silinemiyor',
        'cannot_delete_multiple_body' => ':count yumurtalarının ilişkili sunucuları var ve atlandı.',
    ],

];
