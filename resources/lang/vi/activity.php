<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Người dùng hệ thống',
        'system' => 'Hệ thống',
        'using-api-key' => 'Sử dụng khóa API',
        'using-sftp' => 'Sử dụng SFTP',
    ],
    'auth' => [
        'fail' => 'Đăng nhập không thành công',
        'success' => 'Đã đăng nhập',
        'password-reset' => 'Đặt lại mật khẩu',
        'reset-password' => 'Yêu cầu đặt lại mật khẩu',
        'checkpoint' => 'Đã yêu cầu xác thực hai yếu tố',
        'recovery-token' => 'Mã thông báo khôi phục hai yếu tố đã sử dụng',
        'token' => 'Đã giải quyết thách thức hai yếu tố',
        'ip-blocked' => 'Yêu cầu bị chặn từ địa chỉ IP không công khai đối với :identifier',
        'sftp' => [
            'fail' => 'Đăng nhập SFTP không thành công',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'Đã thay đổi email từ :old thành :new',
            'password-changed' => 'Đã thay đổi mật khẩu',
            'language-changed' => 'Đã thay đổi ngôn ngữ từ :old thành :new',
        ],
        'api-key' => [
            'create' => 'Đã tạo khóa API mới :identifier',
            'delete' => 'Đã xóa khóa API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Đã thêm khóa SSH :fingerprint vào tài khoản',
            'delete' => 'Đã xóa khóa SSH :fingerprint khỏi tài khoản',
        ],
        'two-factor' => [
            'create' => 'Đã bật xác thực hai yếu tố',
            'delete' => 'Xác thực hai yếu tố bị vô hiệu hóa',
        ],
    ],
    'server' => [
        'reinstall' => 'Máy chủ được cài đặt lại',
        'console' => [
            'command' => 'Đã thực thi ":command" trên máy chủ',
        ],
        'power' => [
            'start' => 'Đã khởi động máy chủ',
            'stop' => 'Đã dừng máy chủ',
            'restart' => 'Đã khởi động lại máy chủ',
            'kill' => 'Giết quá trình máy chủ',
        ],
        'backup' => [
            'download' => 'Đã tải xuống bản sao lưu :name',
            'delete' => 'Đã xóa bản sao lưu :name',
            'restore' => 'Đã khôi phục bản sao lưu :name (tệp đã xóa: :truncate)',
            'restore-complete' => 'Đã hoàn tất khôi phục bản sao lưu :name',
            'restore-failed' => 'Không thể hoàn tất khôi phục bản sao lưu :name',
            'start' => 'Đã bắt đầu bản sao lưu mới :name',
            'complete' => 'Đã đánh dấu bản sao lưu :name là hoàn tất',
            'fail' => 'Đã đánh dấu bản sao lưu :name là không thành công',
            'lock' => 'Đã khóa bản sao lưu :name',
            'unlock' => 'Đã mở khóa bản sao lưu :name',
        ],
        'database' => [
            'create' => 'Tạo cơ sở dữ liệu mới :name',
            'rotate-password' => 'Đã xoay mật khẩu cho cơ sở dữ liệu :name',
            'delete' => 'Đã xóa cơ sở dữ liệu :name',
        ],
        'file' => [
            'compress_one' => 'Đã nén :directory:file',
            'compress_other' => 'Các tệp :count được nén trong :directory',
            'read' => 'Đã xem nội dung của :file',
            'copy' => 'Đã tạo bản sao :file',
            'create-directory' => 'Đã tạo thư mục :directory:name',
            'decompress' => 'Đã giải nén :files trong :directory',
            'delete_one' => 'Đã xóa :directory:files.0',
            'delete_other' => 'Đã xóa tệp :count trong :directory',
            'download' => 'Đã tải xuống :file',
            'pull' => 'Đã tải xuống tệp từ xa từ :url về :directory',
            'rename_one' => 'Đã đổi tên :directory:files.0.from thành :directory:files.0.to',
            'rename_other' => 'Đã đổi tên các tệp :count trong :directory',
            'write' => 'Đã viết nội dung mới vào :file',
            'upload' => 'Bắt đầu tải tập tin lên',
            'uploaded' => 'Đã tải lên :directory:file',
        ],
        'sftp' => [
            'denied' => 'Truy cập SFTP bị chặn do quyền',
            'create_one' => 'Đã tạo :files.0',
            'create_other' => 'Đã tạo tệp mới :count',
            'write_one' => 'Đã sửa đổi nội dung của :files.0',
            'write_other' => 'Đã sửa đổi nội dung của tệp :count',
            'delete_one' => 'Đã xóa :files.0',
            'delete_other' => 'Đã xóa tệp :count',
            'create-directory_one' => 'Đã tạo thư mục :files.0',
            'create-directory_other' => 'Đã tạo thư mục :count',
            'rename_one' => 'Đã đổi tên :files.0.from thành :files.0.to',
            'rename_other' => 'Đã đổi tên hoặc di chuyển tệp :count',
        ],
        'allocation' => [
            'create' => 'Đã thêm :allocation vào máy chủ',
            'notes' => 'Đã cập nhật ghi chú cho :allocation từ ":old" thành ":new"',
            'primary' => 'Đặt :allocation làm phân bổ máy chủ chính',
            'delete' => 'Đã xóa phân bổ :allocation',
        ],
        'schedule' => [
            'create' => 'Đã tạo lịch :name',
            'update' => 'Đã cập nhật lịch :name',
            'execute' => 'Thực hiện thủ công lịch trình :name',
            'delete' => 'Đã xóa lịch :name',
        ],
        'task' => [
            'create' => 'Đã tạo tác vụ ":action" mới cho lịch trình :name',
            'update' => 'Đã cập nhật nhiệm vụ ":action" cho lịch trình :name',
            'delete' => 'Đã xóa một nhiệm vụ cho lịch trình :name',
        ],
        'settings' => [
            'rename' => 'Đã đổi tên máy chủ từ :old thành :new',
            'description' => 'Đã thay đổi mô tả máy chủ từ :old thành :new',
        ],
        'startup' => [
            'edit' => 'Đã thay đổi biến :variable từ ":old" thành ":new"',
            'image' => 'Đã cập nhật Docker Image cho máy chủ từ :old lên :new',
        ],
        'subuser' => [
            'create' => 'Đã thêm :email làm người dùng phụ',
            'update' => 'Đã cập nhật quyền của người dùng phụ cho :email',
            'delete' => 'Đã xóa :email với tư cách là người dùng phụ',
        ],
    ],
];
