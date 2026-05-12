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
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Starting',
        'stopping' => 'Stopping',
        'crashed' => 'Crashed',
        'installing' => 'Installing',
        'restoring_backup' => 'Restoring Backup',
        'install_failed' => 'Install Failed',
        'reinstall_failed' => 'Reinstall Failed',
        'suspended' => 'Suspended',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Core Details',
            'allocation' => 'Allocation Management',
            'feature_limits' => 'Application Feature Limits',
            'resources' => 'Resource Management',
            'nest' => 'Nest Configuration',
            'docker' => 'Docker Configuration',
            'startup' => 'Startup Configuration',
            'variables' => 'Service Variables',
        ],

        'fields' => [
            'name' => [
                'label' => 'Server Name',
                'placeholder' => 'Server Name',
                'helper' => 'Character limits: a-z A-Z 0-9 _ - . and spaces.',
            ],
            'owner' => [
                'label' => 'Server Owner',
                'helper' => 'Email address of the Server Owner.',
            ],
            'description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'start_on_completion' => [
                'label' => 'Start Server when Installed',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be deployed to.',
            ],
            'allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on creation.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'cpu' => [
                'label' => 'CPU Limit',
                'helper' => 'Set 0 for no CPU limit. A full virtual core is 100%.',
            ],
            'threads' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: use a single number or comma separated list, for example 0, 0-1,3, or 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk' => [
                'label' => 'Disk Space',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Enable OOM Killer',
                'helper' => 'Terminates the server if it breaches memory limits.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Select the Nest that this server will be grouped under.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Select the Egg that will define how this server should operate.',
            ],
            'skip_scripts' => [
                'label' => 'Skip Egg Install Script',
                'helper' => 'If the selected Egg has an install script attached to it, the script will run during install unless this is checked.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Docker Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the default Docker image that will be used to run this server.',
            ],
            'startup' => [
                'label' => 'Startup Command',
                'helper' => 'Available substitutes: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Select an egg to configure service variables',
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
            'label' => 'Nest',
            'helper' => 'Bu sunucu için hizmet nest\'i.',
        ],
        'egg' => [
            'label' => 'Egg',
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
        'egg' => 'Egg',
        'memory' => 'Bellek',
        'disk' => 'Disk',
        'cpu' => 'CPU',
        'created' => 'Oluşturuldu',
        'updated' => 'Güncellendi',
        'installed' => 'Kuruldu',
        'no_status' => 'Durum Yok',
        'unlimited' => 'Unlimited',
    ],

    'messages' => [
        'created' => 'Sunucu başarıyla oluşturuldu.',
        'updated' => 'Sunucu başarıyla güncellendi.',
        'deleted' => 'Sunucu başarıyla silindi.',
    ],

    'actions' => [
        'edit' => 'Düzenle',
        'random' => 'Random',
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
        'primary_allocation_updated' => 'Primary allocation updated.',
        'database_created' => 'Database created.',
        'database_password_reset' => 'Database password reset.',
        'database_deleted' => 'Database deleted.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build Configuration',
            'startup' => 'Startup',
            'manage' => 'Manage',
        ],

        'sections' => [
            'resource_management' => 'Resource Management',
            'application_feature_limits' => 'Application Feature Limits',
            'allocation_management' => 'Allocation Management',
            'startup_command_modification' => 'Startup Command Modification',
            'service_configuration' => 'Service Configuration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Service Variables',
            'reinstall_server' => 'Reinstall Server',
            'install_status' => 'Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'delete_server' => 'Delete Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Changing these values can trigger a reinstall. The server will be stopped immediately for that operation.',
            'reinstall_server' => 'This will reinstall the server with the assigned service scripts. This could overwrite server data.',
            'install_status' => 'Change install status from uninstalled to installed, or vice versa.',
            'suspend_server' => 'This will stop running processes and block the user from managing the server through the panel or API.',
            'unsuspend_server' => 'This will unsuspend the server and restore normal user access.',
            'transfer_server_transferring' => 'This server is currently being transferred to another node.',
            'transfer_server' => 'Transfer this server to another node connected to this panel.',
            'delete_server' => 'This permanently deletes the server from the panel and Agent. Force delete skips Agent deletion if necessary.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Server Name',
                'helper' => 'Character limits: a-zA-Z0-9_-, spaces, and standard printable characters.',
            ],
            'server_owner' => [
                'label' => 'Server Owner',
                'helper' => 'Changing ownership automatically revokes daemon tokens for the previous owner.',
            ],
            'server_description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (Short)',
            ],
            'external_identifier' => [
                'label' => 'External Identifier',
                'helper' => 'Leave empty to not assign an external identifier. The external ID should be unique to this server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'The default connection address that will be used for this game server.',
            ],
            'additional_ports' => [
                'label' => 'Additional Ports',
                'helper' => 'Assign or remove extra ports. Identical ports on different IPs cannot be assigned to the same server.',
            ],
            'startup_command' => [
                'label' => 'Startup Command',
                'helper' => 'Available by default: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Default Startup Command',
                'error' => 'ERROR: Startup Not Defined!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Limit',
                'helper' => 'Each virtual core is 100%. Set 0 for unrestricted CPU time.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: leave blank for all cores. Examples: 0, 0-1,3, or 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Allocated Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'allocated_swap' => [
                'label' => 'Allocated Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Space Limit',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'block_io_proportion' => [
                'label' => 'Block IO Proportion',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Disable OOM Killer',
                'helper' => 'Enabling OOM killer may cause server processes to exit unexpectedly.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the Docker image that will be used to run this server.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be transferred to.',
            ],
            'transfer_allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstall Server',
            'toggle_install_status' => 'Toggle Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'confirm' => 'Confirm',
            'delete_server' => 'Delete Server',
            'forcibly_delete_server' => 'Forcibly Delete Server',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primary',
            'notes' => 'Notes',
            'created' => 'Created',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'No Alias Assigned',
        ],

        'actions' => [
            'make_primary' => 'Make Primary',
        ],
    ],

    'databases' => [
        'title' => 'Databases',

        'table' => [
            'database' => 'Database',
            'username' => 'Username',
            'remote' => 'Remote',
            'host' => 'Host',
            'max_connections' => 'Max Connections',
            'created' => 'Created',
        ],

        'placeholder' => [
            'unlimited' => 'Unlimited',
        ],

        'actions' => [
            'create_database' => 'Create Database',
            'reset_password' => 'Reset Password',
            'delete' => 'Delete',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Database Name',
                'helper' => 'The panel will prefix this with the server ID, matching the old admin panel.',
            ],
            'database_host' => [
                'label' => 'Database Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max Connections',
            ],
        ],
    ],
];
