<?php

return [
    'title' => 'người dùng',
    'exceptions' => [
        'delete_self' => 'Bạn không thể xóa tài khoản của chính mình.',
        'user_has_servers' => 'Không thể xóa người dùng có máy chủ đang hoạt động được gắn vào tài khoản của họ. Vui lòng xóa máy chủ của họ trước khi tiếp tục.',
    ],
    'notices' => [
        'account_created' => 'Tài khoản đã được tạo thành công.',
        'account_updated' => 'Tài khoản đã được cập nhật thành công.',
    ],
    'details' => [
        'account_details' => 'Chi tiết tài khoản',
        'external_id' => 'ID bên ngoài',
        'username' => 'Tên người dùng',
        'email' => 'Địa chỉ email',
        'first_name' => 'Tên',
        'last_name' => 'Họ',
        'language' => 'Ngôn ngữ',
        'geolocate' => 'Định vị địa lý (Tự động)',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'root_admin' => 'Quản trị viên gốc',
        'root_admin_desc' => 'Người dùng này sẽ có toàn quyền truy cập vào tất cả các máy chủ và cài đặt trên hệ thống.',
        'privileges' => 'Đặc quyền',
        'admin_status' => 'Trạng thái quản trị viên',
    ],
];
