<?php

return [
    'daemon_connection_failed' => 'Đã xảy ra ngoại lệ khi cố gắng giao tiếp với daemon dẫn đến mã phản hồi HTTP/:code. Ngoại lệ này đã được ghi lại.',
    'node' => [
        'servers_attached' => 'Một nút phải không có máy chủ nào được liên kết với nó để có thể bị xóa.',
        'daemon_off_config_updated' => 'Cấu hình daemon đã được cập nhật, tuy nhiên đã xảy ra lỗi khi cố gắng tự động cập nhật tệp cấu hình trên Daemon. Bạn sẽ cần cập nhật thủ công tệp cấu hình (config.yml) để daemon áp dụng những thay đổi này.',
    ],
    'allocations' => [
        'server_using' => 'Một máy chủ hiện được chỉ định cho việc phân bổ này. Việc phân bổ chỉ có thể bị xóa nếu hiện tại không có máy chủ nào được chỉ định.',
        'too_many_ports' => 'Việc thêm hơn 1000 cổng trong một phạm vi cùng một lúc không được hỗ trợ.',
        'invalid_mapping' => 'Ánh xạ được cung cấp cho :port không hợp lệ và không thể xử lý được.',
        'cidr_out_of_range' => 'Ký hiệu CIDR chỉ cho phép mặt nạ nằm trong khoảng từ/25 đến/32.',
        'port_out_of_range' => 'Các cổng trong phân bổ phải lớn hơn 1024 và nhỏ hơn hoặc bằng 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Không thể xóa Nest có máy chủ đang hoạt động được gắn vào nó khỏi Bảng điều khiển.',
        'egg' => [
            'delete_has_servers' => 'Không thể xóa Trứng có máy chủ đang hoạt động gắn liền với nó khỏi Bảng điều khiển.',
            'invalid_copy_id' => 'Trứng được chọn để sao chép tập lệnh không tồn tại hoặc đang sao chép chính tập lệnh.',
            'must_be_child' => 'Lệnh "Sao chép cài đặt từ" cho Trứng này phải là tùy chọn con cho Nest đã chọn.',
            'has_children' => 'Trứng này là cha mẹ của một hoặc nhiều Trứng khác. Vui lòng xóa những quả trứng đó trước khi xóa quả trứng này.',
        ],
        'variables' => [
            'env_not_unique' => 'Biến môi trường :name phải là duy nhất cho Trứng này.',
            'reserved_name' => 'Biến môi trường :name được bảo vệ và không thể gán cho một biến.',
            'bad_validation_rule' => 'Quy tắc xác thực ":rule" không phải là quy tắc hợp lệ cho ứng dụng này.',
        ],
        'importer' => [
            'json_error' => 'Đã xảy ra lỗi khi cố phân tích cú pháp tệp JSON: :error.',
            'file_error' => 'Tệp JSON được cung cấp không hợp lệ.',
            'invalid_json_provided' => 'Tệp JSON được cung cấp không ở định dạng có thể nhận dạng được.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Không được phép chỉnh sửa tài khoản người dùng phụ của riêng bạn.',
        'user_is_owner' => 'Bạn không thể thêm chủ sở hữu máy chủ làm người dùng phụ cho máy chủ này.',
        'subuser_exists' => 'Người dùng có địa chỉ email đó đã được chỉ định làm người dùng phụ cho máy chủ này.',
    ],
    'databases' => [
        'delete_has_databases' => 'Không thể xóa máy chủ lưu trữ cơ sở dữ liệu có cơ sở dữ liệu đang hoạt động được liên kết với nó.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'Khoảng thời gian tối đa cho một nhiệm vụ được xâu chuỗi là 15 phút.',
    ],
    'locations' => [
        'has_nodes' => 'Không thể xóa vị trí có các nút hoạt động gắn liền với nó.',
    ],
    'users' => [
        'node_revocation_failed' => 'Không thu hồi được khóa trên <a href=":link">Nút #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Không tìm thấy nút nào đáp ứng các yêu cầu được chỉ định để triển khai tự động.',
        'no_viable_allocations' => 'Không tìm thấy phân bổ nào đáp ứng yêu cầu triển khai tự động.',
    ],
    'api' => [
        'resource_not_found' => 'Tài nguyên được yêu cầu không tồn tại trên máy chủ này.',
    ],
    'social' => [
        'unlink_only_login' => 'Bạn không thể hủy liên kết phương thức đăng nhập duy nhất của mình nếu không đặt mật khẩu trước.',
    ],
];
