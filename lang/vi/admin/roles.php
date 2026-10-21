<?php

return [
    'label' => 'Vai trò',
    'plural-label' => 'Vai trò',
    'all' => 'Tất cả',
    'select_all' => 'Chọn tất cả',
    'deselect_all' => 'Bỏ chọn tất cả',
    'details' => [
        'title' => 'Chi tiết vai trò',
        'description' => 'Vai trò quyết định người dùng có thể làm gì trên bảng điều khiển, trong khu vực quản trị và trên các máy chủ không thuộc về họ.',
        'name' => 'Tên',
        'name_placeholder' => 'Nhóm hỗ trợ',
        'description_label' => 'Mô tả',
        'description_placeholder' => 'Vai trò này dùng để làm gì.',
    ],
    'table' => [
        'name' => 'Tên',
        'permissions' => 'Quyền',
        'users' => 'Người dùng',
        'updated' => 'Cập nhật lần cuối',
    ],
    'groups' => [
        'management' => [
            'title' => 'Sự quản lý',
            'description' => 'Máy chủ, những người sử dụng chúng và hạ tầng mà chúng chạy trên đó.',
        ],
        'service' => [
            'title' => 'Quản lý dịch vụ',
            'description' => 'Những gì có thể cài đặt trên máy chủ và cách bảng điều khiển được mở rộng và cập nhật.',
        ],
        'administration' => [
            'title' => 'Sự quản lý',
            'description' => 'Cấu hình, giao diện và thông tin tổng quan của toàn bộ bảng điều khiển.',
        ],
    ],
    'resources' => [
        'servers' => 'Máy chủ',
        'users' => 'Người dùng',
        'roles' => 'Vai trò',
        'nodes' => 'Nút',
        'locations' => 'Địa điểm',
        'database_hosts' => 'Máy chủ cơ sở dữ liệu',
        'nests' => 'Tổ yến',
        'eggs' => 'Egg',
        'mounts' => 'Gắn kết',
        'alerts' => 'Cảnh báo',
        'webhooks' => 'Webhook',
        'extensions' => 'Tiện ích mở rộng',
        'activity_log' => 'Nhật ký hoạt động',
        'monitoring' => 'Giám sát',
        'settings' => 'Cài đặt',
        'designify' => 'Designify',
        'updates' => 'Cập nhật phần mềm',
    ],
    'actions' => [
        'send_email' => 'Gửi email',
        'read' => 'Xem',
        'create' => 'Tạo',
        'update' => 'Sửa',
        'delete' => 'Xóa',
        'assign' => 'Gán cho người dùng',
        'manage' => 'Quản lý',
        'install' => 'Cài đặt bản cập nhật',
    ],
    'messages' => [
        'in_use' => 'Vai trò này vẫn đang được gán cho người dùng. Hãy gỡ khỏi họ trước khi xóa.',
    ],
    'user' => [
        'roles' => 'Vai trò',
        'roles_self' => 'Bạn không thể thay đổi vai trò của chính mình.',
        'none' => 'Không có',
    ],
];
