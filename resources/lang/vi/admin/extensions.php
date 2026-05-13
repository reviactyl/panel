<?php

return [

    'label' => 'Sự mở rộng',
    'plural-label' => 'Tiện ích mở rộng',

    'columns' => [
        'id' => 'ID',
        'name' => 'Tên',
        'version' => 'Phiên bản',
        'author' => 'Tác giả',
        'enabled' => 'Đã bật',
        'updated' => 'Đã cập nhật',
        'manifest_json' => 'Tệp kê khai JSON',
    ],

    'modals' => [
        'manifest' => 'Bản kê khai tiện ích mở rộng',
    ],

    'actions' => [
        'edit' => 'Biên tập',
        'upload' => 'Tải lên',
        'manifest' => 'Xem bản kê khai',
        'disable' => 'Vô hiệu hóa',
        'enable' => 'Cho phép',
        'delete' => 'Xóa bỏ',
        'close' => 'Đóng',
    ],

    'alerts' => [
        'enabled' => 'Đã bật tiện ích mở rộng.',
        'enable_failed' => 'Không thể bật tiện ích mở rộng.',
        'disabled' => 'Tiện ích mở rộng bị vô hiệu hóa.',
        'disable_failed' => 'Không tắt được tiện ích mở rộng.',
        'uninstalled' => 'Đã gỡ cài đặt tiện ích mở rộng.',
        'uninstall_failed' => 'Không thể gỡ cài đặt tiện ích mở rộng.',
        'could_not_locate_file' => 'Không thể định vị tệp gói đã tải lên.',
        'invalid_file_type' => 'Chỉ cho phép các tệp .rex.',
        'upload_hint' => 'Chỉ cho phép các gói mở rộng .rex.',
        'install_failed' => 'Cài đặt tiện ích mở rộng không thành công.',
        'install_success' => 'Đã cài đặt :name (:version) thành công.',
    ],

];
