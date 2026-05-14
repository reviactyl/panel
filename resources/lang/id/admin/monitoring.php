<?php

return [
    'navigation' => [
        'label' => 'Pemantauan',
        'group' => 'Administrasi',
    ],

    'page' => [
        'title' => 'Pemantauan',
        'heading' => 'Pemantauan Langsung',
    ],

    'actions' => [
        'refresh' => 'Segarkan Data',
    ],

    'selector' => [
        'label' => 'Pilih Node',
        'placeholder' => 'Pilih simpul...',
    ],

    'stats' => [
        'cpu_usage' => 'Penggunaan CPU',
        'cpu_cores' => 'Inti :count tersedia',
        'memory_usage' => 'Penggunaan Memori',
        'disk_usage' => 'Penggunaan Disk',
        'network_traffic' => 'Lalu Lintas Jaringan',
        'uptime' => 'Waktu aktif',
        'last_updated' => 'Terakhir Diperbarui',
        'no_node' => 'Tidak Ada Node yang Dipilih',
        'no_node_desc' => 'Silakan pilih node untuk melihat data pemantauan',
        'no_node_hint' => 'Gunakan dropdown di atas',
        'error' => 'Kesalahan',
        'error_desc' => 'Tidak dapat memuat data pemantauan',
        'error_fetch' => 'Tidak dapat mengambil data dari Agen',
        'error_node_gone' => 'Node sudah tidak ada lagi',
    ],

    'details' => [
        'heading' => 'Detail Sistem',
        'button' => 'Detail',
        'close' => 'Menutup',
        'no_data' => 'Tidak ada data yang tersedia. Pastikan node sedang online.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Jumlah Penggunaan',
        'cpu_cores' => 'Inti',
        'per_core' => 'Penggunaan Per Inti',

        'memory_section' => 'Ingatan',
        'total_memory' => 'Total',
        'used_memory' => 'Digunakan',
        'free_memory' => 'Bebas',
        'available_memory' => 'Tersedia',

        'swap_section' => 'Menukar',
        'swap_none' => 'Tidak ada swap yang dikonfigurasi pada node ini.',
        'swap_total' => 'Total',
        'swap_used' => 'Digunakan',
        'swap_free' => 'Bebas',
        'swap_usage' => 'Penggunaan',

        'network_section' => 'Jaringan',
        'bytes_sent' => 'Byte Terkirim',
        'bytes_recv' => 'Byte Diterima',
        'packets_sent' => 'Paket Terkirim',
        'packets_received' => 'Paket Diterima',

        'runtime_section' => 'Waktu proses',
        'go_version' => 'Pergi Versi',
        'arch' => 'Arsitektur',
        'goroutines' => 'Goroutine',
        'uptime' => 'Waktu aktif',
    ],
    'servers' => [
        'heading' => 'Penggunaan Server',
        'no_node' => 'Pilih node untuk melihat penggunaan server.',
        'no_servers' => 'Tidak ada server yang ditemukan pada node ini.',
        'error_fetch' => 'Tidak dapat mengambil data server dari Agen.',
        'col' => [
            'name' => 'pelayan',
            'state' => 'Negara',
            'cpu' => 'CPU',
            'memory' => 'Ingatan',
            'disk' => 'Disk',
            'network' => 'Jaringan',
            'uptime' => 'Waktu aktif',
        ],
        'states' => [
            'running' => 'Berlari',
            'starting' => 'Mulai',
            'stopping' => 'Henti',
            'offline' => 'Luring',
            'crashed' => 'Hancur',
            'unknown' => 'Tidak dikenal',
        ],
    ],
];
