<?php

return [
    'navigation' => [
        'label' => 'Giám sát',
        'group' => 'Sự quản lý',
    ],

    'page' => [
        'title' => 'Giám sát',
        'heading' => 'Giám sát trực tiếp',
    ],

    'actions' => [
        'refresh' => 'Làm mới dữ liệu',
    ],

    'selector' => [
        'label' => 'Chọn nút',
        'placeholder' => 'Chọn một nút...',
    ],

    'stats' => [
        'cpu_usage' => 'Sử dụng CPU',
        'cpu_cores' => 'Có sẵn lõi :count',
        'memory_usage' => 'Sử dụng bộ nhớ',
        'disk_usage' => 'Sử dụng đĩa',
        'network_traffic' => 'Lưu lượng mạng',
        'uptime' => 'Thời gian hoạt động',
        'last_updated' => 'Cập nhật lần cuối',
        'no_node' => 'Không có nút nào được chọn',
        'no_node_desc' => 'Vui lòng chọn một nút để xem dữ liệu giám sát',
        'no_node_hint' => 'Sử dụng menu thả xuống ở trên',
        'error' => 'Lỗi',
        'error_desc' => 'Không thể tải dữ liệu giám sát',
        'error_fetch' => 'Không thể lấy dữ liệu từ Đại lý',
        'error_node_gone' => 'Nút không còn tồn tại',
    ],

    'details' => [
        'heading' => 'Chi tiết hệ thống',
        'button' => 'Chi tiết',
        'close' => 'Đóng',
        'no_data' => 'Không có dữ liệu có sẵn. Đảm bảo nút đang trực tuyến.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Tổng mức sử dụng',
        'cpu_cores' => 'lõi',
        'per_core' => 'Cách sử dụng trên mỗi lõi',

        'memory_section' => 'Ký ức',
        'total_memory' => 'Tổng cộng',
        'used_memory' => 'Đã sử dụng',
        'free_memory' => 'Miễn phí',
        'available_memory' => 'Có sẵn',

        'swap_section' => 'Tráo đổi',
        'swap_none' => 'Không có trao đổi nào được định cấu hình trên nút này.',
        'swap_total' => 'Tổng cộng',
        'swap_used' => 'Đã sử dụng',
        'swap_free' => 'Miễn phí',
        'swap_usage' => 'Cách sử dụng',

        'network_section' => 'Mạng',
        'bytes_sent' => 'Byte đã gửi',
        'bytes_recv' => 'Byte đã nhận',
        'packets_sent' => 'Gói đã gửi',
        'packets_received' => 'Gói đã nhận',

        'runtime_section' => 'Thời gian chạy',
        'go_version' => 'Phiên bản đi',
        'arch' => 'Ngành kiến ​​​​trúc',
        'goroutines' => 'Goroutine',
        'uptime' => 'Thời gian hoạt động',
    ],
    'servers' => [
        'heading' => 'Sử dụng máy chủ',
        'no_node' => 'Chọn một nút để xem việc sử dụng máy chủ.',
        'no_servers' => 'Không tìm thấy máy chủ nào trên nút này.',
        'error_fetch' => 'Không thể tìm nạp dữ liệu máy chủ từ Đại lý.',
        'col' => [
            'name' => 'Máy chủ',
            'state' => 'Tình trạng',
            'cpu' => 'CPU',
            'memory' => 'Ký ức',
            'disk' => 'đĩa',
            'network' => 'Mạng',
            'uptime' => 'Thời gian hoạt động',
        ],
        'states' => [
            'running' => 'Đang chạy',
            'starting' => 'Bắt đầu',
            'stopping' => 'Đang dừng',
            'offline' => 'Ngoại tuyến',
            'crashed' => 'Bị hỏng',
            'unknown' => 'Không xác định',
        ],
    ],
];
