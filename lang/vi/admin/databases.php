<?php

return [

    'label' => 'Cơ sở dữ liệu',
    'plural-label' => 'Cơ sở dữ liệu',

    'none' => 'Không có',

    'sections' => [
        'host_details' => [
            'title' => 'Chi tiết máy chủ',
            'description' => 'Định cấu hình cài đặt kết nối máy chủ cơ sở dữ liệu.',
        ],

        'authentication' => [
            'title' => 'Xác thực',
        ],

        'linked_node' => [
            'title' => 'Nút được liên kết',
        ],
    ],

    'placeholders' => [
        'name' => 'MySQL sản xuất',
    ],

    'helpers' => [
        'host' => 'Tên máy chủ hoặc địa chỉ IP của máy chủ cơ sở dữ liệu.',
        'linked_node' => 'Không bắt buộc. Liên kết máy chủ này với một nút cụ thể.',
    ],

    'fields' => [
        'linked_node' => 'Nút được liên kết',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Tên',
        'host' => 'Chủ nhà',
        'port' => 'Cảng',
        'username' => 'Tên người dùng',
        'linked_node' => 'Nút được liên kết',
        'databases' => 'Cơ sở dữ liệu',
        'created' => 'Tạo',
    ],

    'actions' => [
        'edit' => 'Biên tập',
        'delete' => 'Xóa bỏ',
    ],

    'errors' => [
        'cannot_delete' => 'Không thể xóa máy chủ cơ sở dữ liệu có cơ sở dữ liệu liên quan.',
    ],

];
