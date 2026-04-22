<?php

return [
    'navigation' => [
        'label' => 'İzleme',
        'group' => 'Yönetim',
    ],

    'page' => [
        'title' => 'İzleme',
        'heading' => 'Canlı İzleme',
    ],

    'actions' => [
        'refresh' => 'Verileri Yenile',
    ],

    'selector' => [
        'label' => 'Düğüm Seç',
        'placeholder' => 'Bir düğüm seçin...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU Kullanımı',
        'cpu_cores' => ':count çekirdek kullanılabilir',
        'memory_usage' => 'Bellek Kullanımı',
        'disk_usage' => 'Disk Kullanımı',
        'network_traffic' => 'Ağ Trafiği',
        'uptime' => 'Çalışma Süresi',
        'goroutines' => ':count goroutine',
        'last_updated' => 'Son Güncelleme',
        'no_node' => 'Düğüm Seçilmedi',
        'no_node_desc' => 'İzleme verilerini görüntülemek için lütfen bir düğüm seçin',
        'no_node_hint' => 'Yukarıdaki açılır menüyü kullanın',
        'error' => 'Hata',
        'error_desc' => 'İzleme verileri yüklenemiyor',
        'error_fetch' => 'Wings ten veri alınamıyor',
        'error_node_gone' => 'Düğüm artık mevcut değil',
    ],

    'details' => [
        'heading' => 'Sistem Detayları',
        'button' => 'Detaylar',
        'close' => 'Kapat',
        'no_data' => 'Veri mevcut değil. Düğümün çevrimiçi olduğundan emin olun.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Toplam Kullanım',
        'cpu_cores' => 'Çekirdekler',
        'per_core' => 'Çekirdek Başına Kullanım',

        'memory_section' => 'Bellek',
        'total_memory' => 'Toplam',
        'used_memory' => 'Kullanılan',
        'free_memory' => 'Boş',
        'available_memory' => 'Kullanılabilir',

        'swap_section' => 'Takas (Swap)',
        'swap_none' => 'Bu düğümde yapılandırılmış takas alanı yok.',
        'swap_total' => 'Toplam',
        'swap_used' => 'Kullanılan',
        'swap_free' => 'Boş',
        'swap_usage' => 'Kullanım',

        'network_section' => 'Ağ',
        'bytes_sent' => 'Gönderilen Bayt',
        'bytes_recv' => 'Alınan Bayt',
        'packets_sent' => 'Gönderilen Paketler',
        'packets_received' => 'Alınan Paketler',

        'runtime_section' => 'Çalışma Zamanı',
        'go_version' => 'Go Sürümü',
        'arch' => 'Mimari',
        'goroutines' => 'Goroutine\'ler',
        'uptime' => 'Çalışma Süresi',
    ],
    'servers' => [
        'heading' => 'Sunucu Kullanımı',
        'no_node' => 'Sunucu kullanımını görüntülemek için bir düğüm seçin.',
        'no_servers' => 'Bu düğümde sunucu bulunamadı.',
        'error_fetch' => 'Wings ten sunucu verileri alınamıyor.',
        'col' => [
            'name' => 'Sunucu',
            'state' => 'Durum',
            'cpu' => 'CPU',
            'memory' => 'Bellek',
            'disk' => 'Disk',
            'network' => 'Ağ',
            'uptime' => 'Çalışma Süresi',
        ],
        'states' => [
            'running' => 'Running',
            'starting' => 'Starting',
            'stopping' => 'Stopping',
            'offline' => 'Offline',
            'crashed' => 'Crashed',
            'unknown' => 'Unknown',
        ],
    ],
];
