<?php

return [
    'label' => 'Sunucu',
    'plural-label' => 'Sunucular',

    'sections' => [
        'identity' => [
            'title' => 'Kimlik',
            'description' => 'Temel sunucu bilgileri ve sahiplik.',
        ],
        'allocation' => [
            'title' => 'Tahsis',
            'description' => 'Bu sunucu için düğüm ve ağ tahsisini seçin.',
        ],
        'startup' => [
            'title' => 'Başlangıç',
            'description' => 'Egg, başlangıç komutu ve Docker görüntüsünü yapılandırın.',
        ],
        'resources' => [
            'title' => 'Kaynak Sınırları',
            'description' => 'Sunucu kaynak sınırlarını tanımlayın.',
        ],
        'feature_limits' => [
            'title' => 'Özellik Sınırları',
            'description' => 'Veritabanlarını, tahsisleri ve yedekleri sınırlayın.',
        ],
        'environment' => [
            'title' => 'Ortam Değişkenleri',
            'description' => 'Seçilen egg için ortam değerlerini ayarlayın.',
        ],
    ],

    'status' => [
        'online' => 'Çevrimiçi',
        'offline' => 'Çevrimdışı',
        'starting' => 'Başlangıç',
        'stopping' => 'Durdurmak',
        'crashed' => 'Çöktü',
        'installing' => 'Kurulum',
        'restoring_backup' => 'Yedeklemeyi Geri Yükleme',
        'install_failed' => 'Yükleme Başarısız Oldu',
        'reinstall_failed' => 'Yeniden Yükleme Başarısız Oldu',
        'suspended' => 'Askıya alınmış',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Temel Detaylar',
            'allocation' => 'Tahsis Yönetimi',
            'feature_limits' => 'Uygulama Özellik Sınırları',
            'resources' => 'Kaynak Yönetimi',
            'nest' => 'Yuva Yapılandırması',
            'docker' => 'Docker Yapılandırması',
            'startup' => 'Başlangıç ​​Yapılandırması',
            'variables' => 'Hizmet Değişkenleri',
        ],

        'fields' => [
            'name' => [
                'label' => 'Sunucu Adı',
                'placeholder' => 'Sunucu Adı',
                'helper' => 'Karakter sınırları: a-z A-Z 0-9 _ - . ve boşluklar.',
            ],
            'owner' => [
                'label' => 'Sunucu Sahibi',
                'helper' => 'Sunucu Sahibinin e-posta adresi.',
            ],
            'description' => [
                'label' => 'Sunucu Açıklaması',
                'helper' => 'Bu sunucunun kısa bir açıklaması.',
            ],
            'start_on_completion' => [
                'label' => 'Yüklendiğinde Sunucuyu Başlat',
            ],
            'node' => [
                'label' => 'Düğüm',
                'helper' => 'Bu sunucunun konuşlandırılacağı düğüm.',
            ],
            'allocation' => [
                'label' => 'Varsayılan Tahsis',
                'helper' => 'Bu sunucuya atanacak ana tahsis.',
            ],
            'additional_allocations' => [
                'label' => 'Ek Tahsis(ler)',
                'helper' => 'Oluşturma sırasında bu sunucuya atanacak ek tahsisler.',
            ],
            'database_limit' => [
                'label' => 'Veritabanı Sınırı',
                'helper' => 'Bir kullanıcının bu sunucu için oluşturmasına izin verilen toplam veritabanı sayısı.',
            ],
            'allocation_limit' => [
                'label' => 'Tahsis Limiti',
                'helper' => 'Bir kullanıcının bu sunucu için oluşturmasına izin verilen tahsislerin toplam sayısı.',
            ],
            'backup_limit' => [
                'label' => 'Yedekleme Limiti',
                'helper' => 'Bu sunucu için oluşturulabilecek toplam yedekleme sayısı.',
            ],
            'cpu' => [
                'label' => 'CPU Sınırı',
                'helper' => 'CPU limiti olmaması için 0\'ı ayarlayın. Tam bir sanal çekirdek %100\'dür.',
            ],
            'threads' => [
                'label' => 'CPU Sabitleme',
                'helper' => 'Gelişmiş: tek bir sayı veya virgülle ayrılmış bir liste kullanın; örneğin 0, 0-1,3 veya 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Hafıza',
                'helper' => 'Bu kapsayıcı için izin verilen maksimum bellek miktarı. Sınırsız için 0\'ı ayarlayın.',
            ],
            'swap' => [
                'label' => 'Takas',
                'helper' => 'Takası devre dışı bırakmak için 0\'ı veya sınırsız takasa izin vermek için -1\'i ayarlayın.',
            ],
            'disk' => [
                'label' => 'Disk Alanı',
                'helper' => 'Sınırsız disk kullanımına izin vermek için 0\'ı ayarlayın.',
            ],
            'io' => [
                'label' => 'Blok GÇ Ağırlığı',
                'helper' => 'Gelişmiş: Çalışan diğer kapsayıcılara göre GÇ performansı. Değer 10 ile 1000 arasında olmalıdır.',
            ],
            'oom_disabled' => [
                'label' => 'OOM Killer\'ı etkinleştir',
                'helper' => 'Bellek sınırlarını ihlal ederse sunucuyu sonlandırır.',
            ],
            'nest' => [
                'label' => 'Yuva',
                'helper' => 'Bu sunucunun altında gruplandırılacağı Yuvayı seçin.',
            ],
            'egg' => [
                'label' => 'Yumurta',
                'helper' => 'Bu sunucunun nasıl çalışması gerektiğini tanımlayacak Yumurtayı seçin.',
            ],
            'skip_scripts' => [
                'label' => 'Egg Kurulum Komut Dosyasını Atla',
                'helper' => 'Seçilen Egg\'e eklenmiş bir yükleme komut dosyası varsa, bu işaretlenmediği sürece komut dosyası yükleme sırasında çalışacaktır.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Açılır menüden bir görsel seçin veya aşağıya özel bir görsel girin.',
            ],
            'custom_image' => [
                'label' => 'Özel Docker Görüntüsü',
                'placeholder' => 'Veya özel bir resim girin...',
                'helper' => 'Bu, bu sunucuyu çalıştırmak için kullanılacak varsayılan Docker görüntüsüdür.',
            ],
            'startup' => [
                'label' => 'Başlatma Komutu',
                'helper' => 'Mevcut yedekler: {{SERVER_MEMORY}}, {{SERVER_IP}} ve {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Hizmet değişkenlerini yapılandırmak için bir yumurta seçin',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Gelişmiş Mod',
            'helper' => 'Ek sunucu yapılandırma seçeneklerini göstermek için geçiş yapın. Sadece ek ayarların sonuçlarını anlıyorsanız açın.',
        ],
        'external_id' => [
            'label' => 'Harici Kimlik',
            'helper' => 'Bu sunucu için isteğe bağlı benzersiz tanımlayıcı.',
        ],
        'owner' => [
            'label' => 'Sahip',
            'helper' => 'Bu sunucuya sahip olan kullanıcıyı seçin.',
        ],
        'name' => [
            'label' => 'İsim',
            'placeholder' => 'Sunucu İsmi',
            'helper' => 'Bu sunucu için kısa bir isim.',
        ],
        'description' => [
            'label' => 'Açıklama',
            'placeholder' => 'Sunucu açıklaması',
            'helper' => 'Bu sunucu için isteğe bağlı açıklama.',
        ],
        'node' => [
            'label' => 'Düğüm',
            'helper' => 'Bu sunucunun dağıtılacağı düğüm.',
        ],
        'allocation' => [
            'label' => 'Birincil Tahsis',
            'helper' => 'Bu sunucu için varsayılan IP/port tahsisi.',
        ],
        'additional_allocations' => [
            'label' => 'Ek Tahsisler',
            'helper' => 'Atanacak isteğe bağlı ekstra tahsisler.',
        ],
        'nest' => [
            'label' => 'Yuva',
            'helper' => 'Bu sunucu için hizmet nest\'i.',
        ],
        'egg' => [
            'label' => 'Yumurta',
            'helper' => 'Sunucu davranışını tanımlayan egg.',
        ],
        'startup' => [
            'label' => 'Başlangıç Komutu',
            'helper' => 'Sunucu için başlangıç komutu.',
        ],
        'image' => [
            'label' => 'Docker Görüntüsü',
            'helper' => 'Bu sunucuyu çalıştırmak için kullanılan Docker görüntüsü.',
            'custom' => 'Özel',
        ],
        'skip_scripts' => [
            'label' => 'Egg Komut Dosyalarını Atla',
            'helper' => 'Oluşturma sırasında egg kurulum komut dosyalarını atla.',
        ],
        'start_on_completion' => [
            'label' => 'Tamamlandığında Başlat',
            'helper' => 'Kurulumdan sonra sunucuyu otomatik olarak başlat.',
        ],
        'memory' => [
            'label' => 'Bellek',
            'helper' => 'Toplam bellek tahsisi. Sınırsız için 0 olarak ayarlayın. (Sınırsız Bellek, Başlangıç Komutu nedeniyle Minecraft Egg`lerinde çalışmaz)',
        ],
        'swap' => [
            'label' => 'Takas (Swap)',
            'helper' => 'Takas belleği tahsisi. Takası devre dışı bırakmak için 0 veya sınırsız takasa izin vermek için -1 olarak ayarlayın.',
        ],
        'disk' => [
            'label' => 'Disk',
            'helper' => 'Disk alanı tahsisi. Sınırsız için 0 olarak ayarlayın.',
        ],
        'io' => [
            'label' => 'IO Ağırlığı',
            'helper' => 'Göreceli disk G/Ç önceliği (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Yüzde cinsinden CPU sınırı. %100 bir tam çekirdek anlamına gelir, %200 iki tam çekirdek anlmaına gelir vb.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Boyutu GiB Cinsinden Girin',
            'helper' => '"GiB" son ekini kullanarak boyutları GiB cinsinden girebilirsiniz (örn. 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'CPU İş Parçacıkları',
            'helper' => 'İsteğe bağlı iş parçacığı sabitleme. Örnek: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'OOM Killer`ı Devre Dışı Bırak',
            'helper' => 'Bellek tükendiğinde çekirdeğin işlemi sonlandırmasını önleyin.',
        ],
        'database_limit' => [
            'label' => 'Veritabanı Sınırı',
            'helper' => 'Maksimum veritabanı sayısı.',
        ],
        'allocation_limit' => [
            'label' => 'Tahsis Sınırı',
            'helper' => 'Maksimum tahsis sayısı.',
        ],
        'backup_limit' => [
            'label' => 'Yedek Sınırı',
            'helper' => 'Maksimum yedek sayısı.',
        ],
        'environment' => [
            'key' => 'Değişken',
            'value' => 'Değer',
            'helper' => 'Bu egg için ortam değişkenleri.',
        ],
        'use_custom_image' => [
            'label' => 'Özel Görüntü Kullan',
            'helper' => 'Egg tarafından sağlanan yerine özel bir Docker görüntüsü kullanmak için geçiş yapın.',
        ],
    ],

    'table' => [
        'id' => 'KİMLİK',
        'name' => 'İsim',
        'owner' => 'Sahip',
        'node' => 'Düğüm',
        'allocation' => 'Tahsis',
        'status' => 'Durum',
        'egg' => 'Yumurta',
        'memory' => 'Bellek',
        'disk' => 'Disk',
        'cpu' => 'CPU',
        'created' => 'Oluşturuldu',
        'updated' => 'Güncellendi',
        'installed' => 'Kuruldu',
        'no_status' => 'Durum Yok',
        'unlimited' => 'Sınırsız',
    ],

    'messages' => [
        'created' => 'Sunucu başarıyla oluşturuldu.',
        'updated' => 'Sunucu başarıyla güncellendi.',
        'deleted' => 'Sunucu başarıyla silindi.',
    ],

    'actions' => [
        'edit' => 'Düzenle',
        'random' => 'Rastgele',
        'toggle_install_status' => 'Kurulum Durumunu Değiştir',
        'suspend' => 'Askıya Al',
        'unsuspend' => 'Askıdan Al',
        'suspended' => 'Askıya Alındı',
        'unsuspended' => 'Askıdan Alındı',
        'reinstall' => 'Yeniden Kur',
        'delete' => 'Sil',
        'delete_forcibly' => 'Zorla Sil',
        'view' => 'Görüntüle',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Bu sunucu için varsayılan tahsisi silmeye çalışıyorsunuz ancak kullanılabilecek yedek bir tahsis yok.',
        'marked_as_failed' => 'Bu sunucu önceki bir kurulumda başarısız olarak işaretlendi. Bu durumda durum değiştirilemez.',
        'bad_variable' => ':name değişkeni ile ilgili bir doğrulama hatası oluştu.',
        'daemon_exception' => 'Daemon ile iletişim kurulurken bir istisna oluştu ve HTTP/:code yanıt kodu alındı. Bu istisna günlüğe kaydedildi. (istek kimliği: :request_id)',
        'default_allocation_not_found' => 'İstenen varsayılan tahsis bu sunucunun tahsisleri arasında bulunamadı.',
    ],

    'alerts' => [
        'install_toggled' => 'Bu sunucu için kurulum durumu değiştirildi.',
        'server_suspended' => 'Sunucu durumu değiştirildi: :action.',
        'server_reinstalled' => 'Bu sunucu şuan başlayan bir yeniden kurulum için sıraya alındı.',
        'server_deleted' => 'Sunucu başarıyla sistemden silindi.',
        'server_delete_failed' => 'Sunucu silinemedi.',
        'startup_changed' => 'Bu sunucu için başlangıç yapılandırması güncellendi. Bu sunucunun nest veya egg\'i değiştirildiyse, şu anda bir yeniden kurulum gerçekleşecektir.',
        'server_created' => 'Sunucu panelde başarıyla oluşturuldu. Lütfen daemon\'ın bu sunucuyu tamamen kurması için birkaç dakika bekleyin.',
        'build_updated' => 'Bu sunucu için yapı detayları güncellendi. Bazı değişikliklerin etkili olması için yeniden başlatma gerekebilir.',
        'suspension_toggled' => 'Sunucu askıya alma durumu :status olarak değiştirildi.',
        'rebuild_on_boot' => 'Bu sunucu bir Docker Konteyneri yeniden oluşturması gerektirecek şekilde işaretlendi. Bu işlem sunucu bir sonraki başlatıldığında gerçekleşecektir.',
        'details_updated' => 'Sunucu detayları başarıyla güncellendi.',
        'docker_image_updated' => 'Bu sunucu için kullanılacak varsayılan Docker görüntüsü başarıyla değiştirildi. Bu değişikliği uygulamak için yeniden başlatma gereklidir.',
        'node_required' => 'Bu panele bir sunucu ekleyebilmeniz için önce en az bir düğüm yapılandırmış olmanız gerekir.',
        'transfer_nodes_required' => 'Sunucuları transfer edebilmeniz için önce en az iki düğüm yapılandırmış olmanız gerekir.',
        'transfer_started' => 'Sunucu transferi başlatıldı.',
        'transfer_not_viable' => 'Seçtiğiniz düğüm, bu sunucuyu barındırmak için gereken disk alanına veya belleğe sahip değil.',
        'primary_allocation_updated' => 'Birincil tahsis güncellendi.',
        'database_created' => 'Veritabanı oluşturuldu.',
        'database_password_reset' => 'Veritabanı parolası sıfırlama.',
        'database_deleted' => 'Veritabanı silindi.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Bilgi',
            'build_configuration' => 'Yapı Yapılandırması',
            'startup' => 'Başlatmak',
            'manage' => 'Üstesinden gelmek',
        ],

        'sections' => [
            'resource_management' => 'Kaynak Yönetimi',
            'application_feature_limits' => 'Uygulama Özellik Sınırları',
            'allocation_management' => 'Tahsis Yönetimi',
            'startup_command_modification' => 'Başlatma Komutu Değişikliği',
            'service_configuration' => 'Hizmet Yapılandırması',
            'docker_image_configuration' => 'Docker Görüntüsü Yapılandırması',
            'service_variables' => 'Hizmet Değişkenleri',
            'reinstall_server' => 'Sunucuyu Yeniden Yükle',
            'install_status' => 'Kurulum Durumu',
            'suspend_server' => 'Sunucuyu Askıya Al',
            'unsuspend_server' => 'Sunucunun Askıya Alınmasını Kaldır',
            'transfer_server' => 'Aktarım Sunucusu',
            'delete_server' => 'Sunucuyu Sil',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Bu değerlerin değiştirilmesi yeniden yüklemeyi tetikleyebilir. Bu işlem için sunucu derhal durdurulacaktır.',
            'reinstall_server' => 'Bu, sunucuyu atanmış hizmet komut dosyalarıyla yeniden yükleyecektir. Bu, sunucu verilerinin üzerine yazılabilir.',
            'install_status' => 'Yükleme durumunu kaldırılmış durumdan yüklü duruma veya tam tersi şekilde değiştirin.',
            'suspend_server' => 'Bu, çalışan işlemleri durduracak ve kullanıcının sunucuyu panel veya API aracılığıyla yönetmesini engelleyecektir.',
            'unsuspend_server' => 'Bu, sunucunun askıya alınmasını kaldıracak ve normal kullanıcı erişimini geri yükleyecektir.',
            'transfer_server_transferring' => 'Bu sunucu şu anda başka bir düğüme aktarılıyor.',
            'transfer_server' => 'Bu sunucuyu bu panele bağlı başka bir düğüme aktarın.',
            'delete_server' => 'Bu, sunucuyu panelden ve Aracıdan kalıcı olarak siler. Silmeye zorla seçeneği, gerekirse Aracı silme işlemini atlar.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Sunucu Adı',
                'helper' => 'Karakter sınırları: a-zA-Z0-9_-, boşluklar ve standart yazdırılabilir karakterler.',
            ],
            'server_owner' => [
                'label' => 'Sunucu Sahibi',
                'helper' => 'Sahipliğin değiştirilmesi, önceki sahibin daemon belirteçlerini otomatik olarak iptal eder.',
            ],
            'server_description' => [
                'label' => 'Sunucu Açıklaması',
                'helper' => 'Bu sunucunun kısa bir açıklaması.',
            ],
            'server_uuid' => [
                'label' => 'Sunucu UUID\'si',
            ],
            'server_uuid_short' => [
                'label' => 'Sunucu UUID\'si (Kısa)',
            ],
            'external_identifier' => [
                'label' => 'Harici Tanımlayıcı',
                'helper' => 'Harici bir tanımlayıcı atamamak için boş bırakın. Harici kimlik bu sunucuya özgü olmalıdır.',
            ],
            'game_port' => [
                'label' => 'Oyun Bağlantı Noktası',
                'helper' => 'Bu oyun sunucusu için kullanılacak varsayılan bağlantı adresi.',
            ],
            'additional_ports' => [
                'label' => 'Ek Bağlantı Noktaları',
                'helper' => 'Ekstra bağlantı noktaları atayın veya kaldırın. Farklı IP\'lerdeki aynı bağlantı noktaları aynı sunucuya atanamaz.',
            ],
            'startup_command' => [
                'label' => 'Başlatma Komutu',
                'helper' => 'Varsayılan olarak kullanılabilir: {{SERVER_MEMORY}}, {{SERVER_IP}} ve {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Varsayılan Başlatma Komutu',
                'error' => 'HATA: Başlangıç ​​Tanımlanmadı!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Sınırı',
                'helper' => 'Her sanal çekirdek %100\'dür. Sınırsız CPU zamanı için 0\'ı ayarlayın.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Sabitleme',
                'helper' => 'Gelişmiş: tüm çekirdekler için boş bırakın. Örnekler: 0, 0-1,3 veya 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Ayrılmış Bellek',
                'helper' => 'Bu kapsayıcı için izin verilen maksimum bellek miktarı. Sınırsız için 0\'ı ayarlayın.',
            ],
            'allocated_swap' => [
                'label' => 'Tahsis Edilmiş Swap',
                'helper' => 'Takası devre dışı bırakmak için 0\'ı veya sınırsız takasa izin vermek için -1\'i ayarlayın.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Alanı Sınırı',
                'helper' => 'Sınırsız disk kullanımına izin vermek için 0\'ı ayarlayın.',
            ],
            'block_io_proportion' => [
                'label' => 'Blok IO Oranı',
                'helper' => 'Gelişmiş: Çalışan diğer kapsayıcılara göre GÇ performansı. Değer 10 ile 1000 arasında olmalıdır.',
            ],
            'disable_oom_killer' => [
                'label' => 'OOM Killer\'ı devre dışı bırak',
                'helper' => 'OOM katilinin etkinleştirilmesi, sunucu işlemlerinin beklenmedik şekilde kapanmasına neden olabilir.',
            ],
            'database_limit' => [
                'label' => 'Veritabanı Sınırı',
                'helper' => 'Bir kullanıcının bu sunucu için oluşturmasına izin verilen toplam veritabanı sayısı.',
            ],
            'allocation_limit' => [
                'label' => 'Tahsis Limiti',
                'helper' => 'Bir kullanıcının bu sunucu için oluşturmasına izin verilen tahsislerin toplam sayısı.',
            ],
            'backup_limit' => [
                'label' => 'Yedekleme Limiti',
                'helper' => 'Bu sunucu için oluşturulabilecek toplam yedekleme sayısı.',
            ],
            'image' => [
                'label' => 'Resim',
                'helper' => 'Açılır menüden bir görsel seçin veya aşağıya özel bir görsel girin.',
            ],
            'custom_image' => [
                'label' => 'Özel Resim',
                'placeholder' => 'Veya özel bir resim girin...',
                'helper' => 'Bu, bu sunucuyu çalıştırmak için kullanılacak Docker görüntüsüdür.',
            ],
            'transfer_node' => [
                'label' => 'Düğüm',
                'helper' => 'Bu sunucunun aktarılacağı düğüm.',
            ],
            'transfer_allocation' => [
                'label' => 'Varsayılan Tahsis',
                'helper' => 'Bu sunucuya atanacak ana tahsis.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Ek Tahsis(ler)',
                'helper' => 'Aktarım sırasında bu sunucuya atanacak ek tahsisler.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Sunucuyu Yeniden Yükle',
            'toggle_install_status' => 'Kurulum Durumunu Değiştir',
            'suspend_server' => 'Sunucuyu Askıya Al',
            'unsuspend_server' => 'Sunucunun Askıya Alınmasını Kaldır',
            'transfer_server' => 'Aktarım Sunucusu',
            'confirm' => 'Onaylamak',
            'delete_server' => 'Sunucuyu Sil',
            'forcibly_delete_server' => 'Sunucuyu Zorla Sil',
        ],
    ],

    'allocations' => [
        'title' => 'Tahsisler',

        'table' => [
            'ip' => 'IP',
            'port' => 'Liman',
            'alias' => 'Takma ad',
            'primary' => 'Öncelik',
            'notes' => 'Notlar',
            'created' => 'Oluşturuldu',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Takma Ad Atanmadı',
        ],

        'actions' => [
            'make_primary' => 'Birincil Yap',
        ],
    ],

    'databases' => [
        'title' => 'Veritabanları',

        'table' => [
            'database' => 'Veritabanı',
            'username' => 'Kullanıcı adı',
            'remote' => 'Uzak',
            'host' => 'Ev sahibi',
            'max_connections' => 'Maksimum Bağlantı',
            'created' => 'Oluşturuldu',
        ],

        'placeholder' => [
            'unlimited' => 'Sınırsız',
        ],

        'actions' => [
            'create_database' => 'Veritabanı Oluştur',
            'reset_password' => 'Şifreyi Sıfırla',
            'delete' => 'Silmek',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Veritabanı Adı',
                'helper' => 'Panel, eski yönetici paneliyle eşleşen sunucu kimliğini buna önek olarak ekleyecektir.',
            ],
            'database_host' => [
                'label' => 'Veritabanı Sunucusu',
            ],
            'remote' => [
                'label' => 'Uzak',
            ],
            'max_connections' => [
                'label' => 'Maksimum Bağlantı',
            ],
        ],
    ],
];
