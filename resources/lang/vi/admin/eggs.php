<?php

return [

    'tabs' => [
        'configuration' => 'Cấu hình trứng',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Cấu hình',
        ],
        'identity' => [
            'title' => 'Danh tính',
        ],
        'docker_images' => [
            'title' => 'Hình ảnh Docker',
            'description' => 'Các hình ảnh docker có sẵn cho các máy chủ sử dụng quả trứng này. Nhập một cái trên mỗi dòng.',
        ],
        'process_management' => [
            'title' => 'Quản lý quy trình',
        ],
        'variables' => [
            'title' => 'Biến',
        ],
        'install_script' => [
            'title' => 'Cài đặt tập lệnh',
        ],
    ],

    'fields' => [
        'nest' => 'Tổ',
        'uuid' => 'UUID',
        'name' => 'Tên',
        'author' => 'Tác giả',
        'image' => 'Hình ảnh',
        'description' => 'Sự miêu tả',
        'image_name' => 'Tên hình ảnh',
        'image_uri' => 'URI hình ảnh',
        'add_docker_image' => 'Thêm hình ảnh Docker',
        'force_outgoing_ip' => 'Buộc IP gửi đi',
        'features' => 'Đặc trưng',
        'startup' => 'Lệnh khởi động',
        'config_stop' => 'Lệnh dừng',
        'config_from' => 'Sao chép cài đặt từ',
        'config_startup' => 'Bắt đầu cấu hình (JSON)',
        'config_logs' => 'Cấu hình nhật ký (JSON)',
        'config_files' => 'Tệp cấu hình (JSON)',
        'file_denylist' => 'Danh sách từ chối tệp',
        'env_variable' => 'Biến môi trường',
        'user_viewable' => 'Người dùng có thể xem',
        'user_editable' => 'Người dùng có thể chỉnh sửa',
        'rules' => 'Quy tắc nhập liệu',
        'default_value' => 'Giá trị mặc định',
        'script_install' => 'Cài đặt tập lệnh',
        'script_container' => 'Vùng chứa tập lệnh',
        'script_entry' => 'Lệnh điểm nhập tập lệnh',
        'copy_script_from' => 'Sao chép tập lệnh từ',
        'script_is_privileged' => 'Đặc quyền',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Buộc tất cả lưu lượng truy cập mạng đi phải có IP nguồn được NAT với IP của IP phân bổ chính của máy chủ.',
        'features' => 'Các tính năng bổ sung thuộc về trứng. Hữu ích cho việc cấu hình các sửa đổi bảng điều khiển bổ sung.',
        'file_denylist' => 'Các tập tin mà người dùng không nên chỉnh sửa.',
        'script_is_privileged' => 'Chạy tập lệnh cài đặt dưới dạng vùng chứa đặc quyền (root).',
    ],

    'actions' => [
        'export' => 'Xuất khẩu',
        'create' => 'Tạo trứng',
        'edit' => 'Biên tập',
    ],

    'notices' => [
        'cannot_delete' => 'Không thể xóa trứng',
        'cannot_delete_body' => 'Quả trứng này có (các) máy chủ :count được liên kết. Vui lòng xóa hoặc gán lại chúng trước.',
        'cannot_delete_multiple' => 'Không thể xóa trứng bằng máy chủ',
        'cannot_delete_multiple_body' => '(Các) trứng :count có máy chủ được liên kết và đã bị bỏ qua.',
    ],

];
