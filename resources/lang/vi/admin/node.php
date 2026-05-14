<?php

return [
    'label' => 'Nút',
    'plural-label' => 'Nút',

    'sections' => [
        'overview' => [
            'title' => 'Tổng quan',
            'information-label' => 'Thông tin nút',
            'version-label' => 'Phiên bản đại lý',
            'architecture-label' => 'Ngành kiến ​​​​trúc',
            'kernel-label' => 'hạt nhân',
            'cpus-label' => 'Chủ đề CPU',
            'cpu-usage-label' => 'Sử dụng CPU',
            'memory-usage-label' => 'Sử dụng bộ nhớ',
            'disk-usage-label' => 'Sử dụng đĩa',
        ],
        'tabs' => [
            'title' => 'Cấu hình nút',
        ],
        'identity' => [
            'title' => 'Danh tính',
            'description' => 'Thông tin nút cơ bản.',
        ],
        'connection' => [
            'title' => 'Chi tiết kết nối',
            'description' => 'Cấu hình cách kết nối với nút này.',
        ],
        'resources' => [
            'title' => 'Phân bổ nguồn lực',
            'description' => 'Xác định giới hạn bộ nhớ và đĩa cho nút này.',
        ],
        'daemon' => [
            'title' => 'Cấu hình daemon',
            'description' => 'Định cấu hình cài đặt dành riêng cho daemon.',
        ],
        'configuration' => [
            'title' => 'Cấu hình',
            'config_description' => 'Tệp cấu hình',
            'deploy_description' => 'Tạo lệnh triển khai tùy chỉnh có thể được sử dụng để đặt cấu hình Tác nhân trên máy chủ đích.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Công cộng',
            'helper' => 'Bằng cách đặt nút ở chế độ riêng tư, bạn sẽ từ chối khả năng tự động triển khai cho nút này.',
        ],
        'name' => [
            'label' => 'Tên',
            'placeholder' => 'Tên nút',
            'helper' => 'Tên mô tả cho nút này.',
        ],
        'description' => [
            'label' => 'Sự miêu tả',
            'placeholder' => 'Mô tả nút',
            'helper' => 'Mô tả tùy chọn cho nút này.',
        ],
        'location' => [
            'label' => 'Vị trí',
            'helper' => 'Vị trí nút này được chỉ định.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'nút.example.com',
            'helper' => 'Tên miền hoặc địa chỉ IP đủ điều kiện.',
        ],
        'ssl' => [
            'label' => 'Sử dụng SSL',
            'helper' => 'Liệu daemon trên nút này có được định cấu hình để sử dụng SSL để liên lạc an toàn hay không.',
            'helper_forced' => 'Bảng điều khiển này đang chạy trên HTTPS nên bắt buộc phải có SSL cho nút này.',
        ],
        'behind_proxy' => [
            'label' => 'Đằng sau proxy',
            'helper' => 'Kích hoạt nếu nút này đứng sau proxy như Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Chế độ bảo trì',
            'helper' => 'Ngăn chặn việc tạo các máy chủ mới trên nút này.',
        ],
        'memory' => [
            'label' => 'Tổng bộ nhớ',
            'helper' => 'Tổng bộ nhớ trong MiB có sẵn trên nút này.',
        ],
        'memory_overallocate' => [
            'label' => 'Phân bổ tổng thể bộ nhớ',
            'helper' => 'Tỷ lệ bộ nhớ để phân bổ tổng thể. Sử dụng -1 để tắt tính năng kiểm tra.',
        ],
        'disk' => [
            'label' => 'Tổng dung lượng đĩa',
            'helper' => 'Tổng dung lượng ổ đĩa trong MiB có sẵn trên nút này.',
        ],
        'disk_overallocate' => [
            'label' => 'Phân bổ quá mức đĩa',
            'helper' => 'Tỷ lệ phần trăm đĩa để phân bổ tổng thể. Sử dụng -1 để tắt tính năng kiểm tra.',
        ],
        'upload_size' => [
            'label' => 'Kích thước tải lên tối đa',
            'helper' => 'Kích thước tải lên tệp tối đa được phép thông qua bảng điều khiển web.',
        ],
        'daemon_base' => [
            'label' => 'Thư mục cơ sở',
            'placeholder' => '/home/tập tin daemon',
            'helper' => 'Thư mục nơi lưu trữ các tập tin máy chủ.',
        ],
        'daemon_listen' => [
            'label' => 'Cảng Daemon',
            'helper' => 'Cổng mà daemon lắng nghe để giao tiếp HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'Cổng SFTP',
            'helper' => 'Cổng được sử dụng cho kết nối SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'ID mã thông báo',
        ],
        'container_text' => [
            'label' => 'Tiền tố vùng chứa',
            'helper' => 'Tiền tố văn bản được hiển thị trong tên vùng chứa.',
        ],
    ],

    'table' => [
        'health' => 'Sức khỏe',
        'health_http_status' => ':status HTTP',
        'health_check_console' => 'kiểm tra bảng điều khiển trình duyệt',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Tên',
        'location' => 'Vị trí',
        'fqdn' => 'FQDN',
        'scheme' => 'Giao thức',
        'public' => 'Công cộng',
        'behind_proxy' => 'Đằng sau proxy',
        'maintenance_mode' => 'BẢO TRÌ',
        'memory' => 'Ký ức',
        'memory_overallocate' => 'Hết bộ nhớ',
        'disk' => 'đĩa',
        'disk_overallocate' => 'Hết đĩa',
        'upload_size' => 'Kích thước tải lên',
        'daemon_listen' => 'Cảng Daemon',
        'daemon_sftp' => 'Cổng SFTP',
        'daemon_base' => 'Thư mục cơ sở',
        'servers' => 'Máy chủ',
        'created' => 'Tạo',
        'updated' => 'Đã cập nhật',
    ],

    'filters' => [
        'public' => 'Công cộng',
        'maintenance' => 'BẢO TRÌ',
        'public_true' => 'Công cộng',
        'public_false' => 'Riêng tư',
        'maintenance_true' => 'Đang bảo trì',
        'maintenance_false' => 'Tích cực',
    ],

    'actions' => [
        'create' => 'Tạo nên',
        'edit' => 'Biên tập',
        'delete' => 'Xóa bỏ',
        'view' => 'Xem',
        'random' => 'Ngẫu nhiên',
        'view_monitoring' => 'Xem giám sát',
    ],

    'deployment' => [
        'generate_label' => 'Tạo mã thông báo triển khai',
        'modal_heading' => 'Lệnh triển khai tự động',
        'modal_description' => 'Chạy lệnh này trên nút của bạn để tự động định cấu hình Tác nhân.',
        'modal_close' => 'Đóng',
        'command_label' => 'Lệnh triển khai',
        'command_helper' => 'Sao chép và chạy lệnh này trên máy chủ nút của bạn.',
        'token_success' => 'Mã thông báo được tạo thành công',
        'token_success_body' => 'Sao chép và chạy lệnh bên dưới trên nút của bạn.',
        'save_first' => 'Vui lòng lưu nút trước.',
        'auto_generated_key' => 'Khóa triển khai nút được tạo tự động.',
        'error' => 'Lỗi tạo mã thông báo. Vui lòng thử lại.',
    ],

    'general' => [
        'na' => 'không áp dụng',
        'unavailable' => 'Không có sẵn',
    ],

    'messages' => [
        'created' => 'Nút đã được tạo thành công.',
        'updated' => 'Nút đã được cập nhật thành công.',
        'deleted' => 'Nút đã được xóa thành công.',
        'cannot_delete_with_servers' => 'Không thể xóa nút có máy chủ đang hoạt động.',
    ],

    'allocations' => [
        'label' => 'Phân bổ',
        'table' => [
            'ip' => 'IP',
            'port' => 'Cảng',
            'alias' => 'Bí danh',
            'server' => 'Máy chủ',
            'notes' => 'Ghi chú',
            'created' => 'Tạo',
            'unassigned' => 'Chưa được chỉ định',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Address',
                'helper' => 'Hỗ trợ một IP hoặc CIDR (ví dụ: 192.0.2.1 hoặc 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Cổng',
                'helper' => 'Nhập cổng hoặc phạm vi (ví dụ: 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'Bí danh IP',
                'helper' => 'Bí danh tùy chọn để hiển thị thay vì IP.',
            ],
        ],
        'actions' => [
            'add' => 'Thêm phân bổ',
            'delete' => 'Xóa bỏ',
        ],
        'messages' => [
            'created' => 'Đã thêm phân bổ.',
            'deleted' => 'Đã xóa phân bổ.',
            'failed' => 'Hành động phân bổ không thành công.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'Địa chỉ FQDN hoặc IP được cung cấp không chuyển thành địa chỉ IP hợp lệ.',
        'fqdn_required_for_ssl' => 'Cần có một tên miền đủ điều kiện phân giải thành địa chỉ IP công cộng để sử dụng SSL cho nút này.',
    ],
    'notices' => [
        'allocations_added' => 'Phân bổ đã được thêm thành công vào nút này.',
        'node_deleted' => 'Nút đã được xóa thành công khỏi bảng điều khiển.',
        'location_required' => 'Bạn phải định cấu hình ít nhất một vị trí trước khi có thể thêm nút vào bảng này.',
        'node_created' => 'Đã tạo thành công nút mới. Bạn có thể tự động định cấu hình daemon trên máy này bằng cách truy cập tab \'Cấu hình\'. Trước khi có thể thêm bất kỳ máy chủ nào, trước tiên bạn phải phân bổ ít nhất một địa chỉ IP và cổng.',
        'node_updated' => 'Thông tin nút đã được cập nhật. Nếu bất kỳ cài đặt daemon nào bị thay đổi, bạn sẽ cần phải khởi động lại nó để những thay đổi đó có hiệu lực.',
        'unallocated_deleted' => 'Đã xóa tất cả các cổng chưa được phân bổ cho <code>:ip</code>.',
    ],
];
