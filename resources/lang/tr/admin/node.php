<?php

return [
    'label' => 'Düğüm',
    'plural-label' => 'Düğümler',

    'sections' => [
        'overview' => [
            'title' => 'Genel Bakış',
            'information-label' => 'Düğüm Bilgileri',
            'version-label' => 'Temsilci Sürümü',
            'architecture-label' => 'Mimarlık',
            'kernel-label' => 'Çekirdek',
            'cpus-label' => 'CPU Konuları',
            'cpu-usage-label' => 'CPU Kullanımı',
            'memory-usage-label' => 'Bellek Kullanımı',
            'disk-usage-label' => 'Disk Kullanımı',
        ],
        'tabs' => [
            'title' => 'Düğüm Yapılandırması',
        ],
        'identity' => [
            'title' => 'Kimlik',
            'description' => 'Temel düğüm bilgileri.',
        ],
        'connection' => [
            'title' => 'Bağlantı Detayları',
            'description' => 'Bu düğüme nasıl bağlanılacağını yapılandırın.',
        ],
        'resources' => [
            'title' => 'Kaynak Tahsisi',
            'description' => 'Bu düğüm için bellek ve disk sınırlarını tanımlayın.',
        ],
        'daemon' => [
            'title' => 'Daemon Yapılandırması',
            'description' => 'Daemon\'a özgü ayarları yapılandırın.',
        ],
        'configuration' => [
            'title' => 'Yapılandırma',
            'config_description' => 'Yapılandırma Dosyası',
            'deploy_description' => 'Hedef sunucuda Agent\'ı yapılandırmak için kullanılabilecek özel bir dağıtım komutu oluşturun.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Açık (Public)',
            'helper' => 'Bir düğümü gizli olarak ayarlayarak bu düğüme otomatik dağıtım yapma özelliğini engellemiş olursunuz.',
        ],
        'name' => [
            'label' => 'İsim',
            'placeholder' => 'Düğüm İsmi',
            'helper' => 'Bu düğüm için açıklayıcı bir isim.',
        ],
        'description' => [
            'label' => 'Açıklama',
            'placeholder' => 'Düğüm açıklaması',
            'helper' => 'Bu düğüm için isteğe bağlı açıklama.',
        ],
        'location' => [
            'label' => 'Konum',
            'helper' => 'Bu düğümün atandığı konum.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'düğüm.example.com',
            'helper' => 'Tam nitelikli alan adı (FQDN) veya IP adresi.',
        ],
        'ssl' => [
            'label' => 'SSL Kullanır',
            'helper' => 'Bu düğümdeki daemon\'ın güvenli iletişim için SSL kullanacak şekilde yapılandırılıp yapılandırılmadığı.',
            'helper_forced' => 'Bu panel HTTPS üzerinde çalışıyor, bu nedenle bu düğüm için SSL zorunludur.',
        ],
        'behind_proxy' => [
            'label' => 'Proxy Arkasında',
            'helper' => 'Bu düğüm Cloudflare gibi bir proxy\'nin arkasındaysa etkinleştirin.',
        ],
        'maintenance_mode' => [
            'label' => 'Bakım Modu',
            'helper' => 'Bu düğümde yeni sunucuların oluşturulmasını önleyin.',
        ],
        'memory' => [
            'label' => 'Toplam Bellek',
            'helper' => 'Bu düğümde kullanılabilir MiB cinsinden toplam bellek.',
        ],
        'memory_overallocate' => [
            'label' => 'Aşırı Bellek Tahsisi',
            'helper' => 'Aşırı tahsis edilecek bellek yüzdesi. Kontrolü devre dışı bırakmak için -1 kullanın.',
        ],
        'disk' => [
            'label' => 'Toplam Disk Alanı',
            'helper' => 'Bu düğümde kullanılabilir MiB cinsinden toplam disk alanı.',
        ],
        'disk_overallocate' => [
            'label' => 'Aşırı Disk Tahsisi',
            'helper' => 'Aşırı tahsis edilecek disk yüzdesi. Kontrolü devre dışı bırakmak için -1 kullanın.',
        ],
        'upload_size' => [
            'label' => 'Maksimum Yükleme Boyutu',
            'helper' => 'Web paneli üzerinden izin verilen maksimum dosya yükleme boyutu.',
        ],
        'daemon_base' => [
            'label' => 'Temel Dizin',
            'placeholder' => '/home/daemon-dosyaları',
            'helper' => 'Sunucu dosyalarının depolandığı dizin.',
        ],
        'daemon_listen' => [
            'label' => 'Daemon Portu',
            'helper' => 'Daemon\'ın HTTP iletişimi için dinlediği port.',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP Portu',
            'helper' => 'SFTP bağlantıları için kullanılan port.',
        ],
        'daemon_token_id' => [
            'label' => 'Jeton Kimliği (Token ID)',
        ],
        'container_text' => [
            'label' => 'Konteyner Öneki',
            'helper' => 'Konteyner isimlerinde görüntülenen metin öneki.',
        ],
    ],

    'table' => [
        'health' => 'Sağlık',
        'health_http_status' => 'HTTP :status',
        'health_check_console' => 'tarayıcı konsolunu kontrol edin',
        'id' => 'KİMLİK',
        'uuid' => 'UUID',
        'name' => 'İsim',
        'location' => 'Konum',
        'fqdn' => 'FQDN',
        'scheme' => 'Protokol',
        'public' => 'Açık',
        'behind_proxy' => 'Proxy Arkasında',
        'maintenance_mode' => 'Bakım',
        'memory' => 'Bellek',
        'memory_overallocate' => 'Bellek (Aşırı)',
        'disk' => 'Disk',
        'disk_overallocate' => 'Disk (Aşırı)',
        'upload_size' => 'Yükleme Boyutu',
        'daemon_listen' => 'Daemon Portu',
        'daemon_sftp' => 'SFTP Portu',
        'daemon_base' => 'Temel Dizin',
        'servers' => 'Sunucular',
        'created' => 'Oluşturuldu',
        'updated' => 'Güncellendi',
    ],

    'filters' => [
        'public' => 'Halk',
        'maintenance' => 'Bakım',
        'public_true' => 'Halk',
        'public_false' => 'Özel',
        'maintenance_true' => 'Bakımda',
        'maintenance_false' => 'Aktif',
    ],

    'actions' => [
        'create' => 'Oluştur',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
        'view' => 'Görüntüle',
        'random' => 'Rastgele',
        'view_monitoring' => 'İzlemeyi Görüntüle',
    ],

    'deployment' => [
        'generate_label' => 'Dağıtım Jetonu Oluştur',
        'modal_heading' => 'Otomatik Dağıtım Komutu',
        'modal_description' => 'Agent\'ı otomatik olarak yapılandırmak için bu komutu düğümünüzde çalıştırın.',
        'modal_close' => 'Kapalı',
        'command_label' => 'Dağıtım Komutanlığı',
        'command_helper' => 'Bu komutu düğüm sunucunuza kopyalayıp çalıştırın.',
        'token_success' => 'Jeton Başarıyla Oluşturuldu',
        'token_success_body' => 'Aşağıdaki komutu düğümünüze kopyalayıp çalıştırın.',
        'save_first' => 'Lütfen önce düğümü kaydedin.',
        'auto_generated_key' => 'Otomatik olarak oluşturulan düğüm dağıtım anahtarı.',
        'error' => 'Belirteç oluşturulurken hata oluştu. Lütfen tekrar deneyin.',
    ],

    'general' => [
        'na' => 'Yok',
        'unavailable' => 'Kullanılamıyor',
    ],

    'messages' => [
        'created' => 'Düğüm başarıyla oluşturuldu.',
        'updated' => 'Düğüm başarıyla güncellendi.',
        'deleted' => 'Düğüm başarıyla silindi.',
        'cannot_delete_with_servers' => 'Aktif sunucuları olan bir düğüm silinemez.',
    ],

    'allocations' => [
        'label' => 'Tahsisler',
        'table' => [
            'ip' => 'IP',
            'port' => 'Liman',
            'alias' => 'Takma Ad',
            'server' => 'Sunucu',
            'notes' => 'Notlar',
            'created' => 'Oluşturuldu',
            'unassigned' => 'Atanmamış',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Adresi',
                'helper' => 'Tek IP veya CIDR (örn. 192.0.2.1 veya 192.0.2.0/24) destekler.',
            ],
            'allocation_ports' => [
                'label' => 'Portlar',
                'helper' => 'Portları veya aralıkları girin (örn. 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP Takma Adı',
                'helper' => 'IP yerine görüntülenecek isteğe bağlı takma ad.',
            ],
        ],
        'actions' => [
            'add' => 'Tahsis Ekle',
            'delete' => 'Sil',
        ],
        'messages' => [
            'created' => 'Tahsisler eklendi.',
            'deleted' => 'Tahsis silindi.',
            'failed' => 'Tahsis işlemi başarısız oldu.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'Sağlanan FQDN veya IP adresi geçerli bir IP adresine çözümlenemiyor.',
        'fqdn_required_for_ssl' => 'Bu düğüm için SSL kullanabilmek amacıyla genel bir IP adresine çözümlenen tam nitelikli bir alan adı (FQDN) gereklidir.',
    ],
    'notices' => [
        'allocations_added' => 'Tahsisler bu düğüme başarıyla eklendi.',
        'node_deleted' => 'Düğüm başarıyla panelden kaldırıldı.',
        'location_required' => 'Bu panele bir düğüm ekleyebilmeniz için önce en az bir konum yapılandırmış olmanız gerekir.',
        'node_created' => 'Yeni düğüm başarıyla oluşturuldu. \`Yapılandırma\` sekmesini ziyaret ederek bu makinedeki daemon\'ı otomatik olarak yapılandırabilirsiniz. Herhangi bir sunucu ekleyebilmeniz için önce en az bir IP adresi ve port tahsis etmeniz gerekir.',
        'node_updated' => 'Düğüm bilgileri güncellendi. Herhangi bir daemon ayarı değiştirildiyse, bu değişikliklerin etkili olması için onu yeniden başlatmanız gerekecektir.',
        'unallocated_deleted' => '<code>:ip</code> için tahsis edilmemiş tüm portlar silindi.',
    ],
];
