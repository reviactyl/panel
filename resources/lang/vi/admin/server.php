<?php

return [
    'label' => 'Máy chủ',
    'plural-label' => 'Máy chủ',

    'sections' => [
        'identity' => [
            'title' => 'Danh tính',
            'description' => 'Thông tin máy chủ cơ bản và quyền sở hữu.',
        ],
        'allocation' => [
            'title' => 'Phân bổ',
            'description' => 'Chọn nút và phân bổ mạng cho máy chủ này.',
        ],
        'startup' => [
            'title' => 'Khởi động',
            'description' => 'Định cấu hình trứng, lệnh khởi động và hình ảnh Docker.',
        ],
        'resources' => [
            'title' => 'Giới hạn tài nguyên',
            'description' => 'Xác định giới hạn tài nguyên máy chủ.',
        ],
        'feature_limits' => [
            'title' => 'Giới hạn tính năng',
            'description' => 'Giới hạn cơ sở dữ liệu, phân bổ và sao lưu.',
        ],
        'environment' => [
            'title' => 'Biến môi trường',
            'description' => 'Đặt giá trị môi trường cho quả trứng đã chọn.',
        ],
    ],

    'status' => [
        'online' => 'Trực tuyến',
        'offline' => 'Ngoại tuyến',
        'starting' => 'Bắt đầu',
        'stopping' => 'Đang dừng',
        'crashed' => 'Bị hỏng',
        'installing' => 'Đang cài đặt',
        'restoring_backup' => 'Khôi phục bản sao lưu',
        'install_failed' => 'Cài đặt không thành công',
        'reinstall_failed' => 'Cài đặt lại không thành công',
        'suspended' => 'Cấm',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Chi tiết cốt lõi',
            'allocation' => 'Quản lý phân bổ',
            'feature_limits' => 'Giới hạn tính năng ứng dụng',
            'resources' => 'Quản lý tài nguyên',
            'nest' => 'Cấu hình tổ',
            'docker' => 'Cấu hình Docker',
            'startup' => 'Cấu hình khởi động',
            'variables' => 'Biến dịch vụ',
        ],

        'fields' => [
            'name' => [
                'label' => 'Tên máy chủ',
                'placeholder' => 'Tên máy chủ',
                'helper' => 'Giới hạn ký tự: a-z A-Z 0-9 _ - . và không gian.',
            ],
            'owner' => [
                'label' => 'Chủ sở hữu máy chủ',
                'helper' => 'Địa chỉ email của Chủ sở hữu máy chủ.',
            ],
            'description' => [
                'label' => 'Mô tả máy chủ',
                'helper' => 'Một mô tả ngắn gọn về máy chủ này.',
            ],
            'start_on_completion' => [
                'label' => 'Khởi động máy chủ khi được cài đặt',
            ],
            'node' => [
                'label' => 'Nút',
                'helper' => 'Nút mà máy chủ này sẽ được triển khai tới.',
            ],
            'allocation' => [
                'label' => 'Phân bổ mặc định',
                'helper' => 'Sự phân bổ chính sẽ được gán cho máy chủ này.',
            ],
            'additional_allocations' => [
                'label' => '(Các) Phân bổ bổ sung',
                'helper' => 'Phân bổ bổ sung để gán cho máy chủ này khi tạo.',
            ],
            'database_limit' => [
                'label' => 'Giới hạn cơ sở dữ liệu',
                'helper' => 'Tổng số cơ sở dữ liệu mà người dùng được phép tạo cho máy chủ này.',
            ],
            'allocation_limit' => [
                'label' => 'Giới hạn phân bổ',
                'helper' => 'Tổng số phân bổ mà người dùng được phép tạo cho máy chủ này.',
            ],
            'backup_limit' => [
                'label' => 'Giới hạn dự phòng',
                'helper' => 'Tổng số bản sao lưu có thể được tạo cho máy chủ này.',
            ],
            'cpu' => [
                'label' => 'Giới hạn CPU',
                'helper' => 'Đặt 0 nếu không có giới hạn CPU. Một lõi ảo đầy đủ là 100%.',
            ],
            'threads' => [
                'label' => 'Ghim CPU',
                'helper' => 'Nâng cao: sử dụng một số hoặc danh sách được phân tách bằng dấu phẩy, ví dụ 0, 0-1,3 hoặc 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Ký ức',
                'helper' => 'Dung lượng bộ nhớ tối đa được phép cho vùng chứa này. Đặt 0 cho không giới hạn.',
            ],
            'swap' => [
                'label' => 'Tráo đổi',
                'helper' => 'Đặt 0 để tắt trao đổi hoặc -1 để cho phép trao đổi không giới hạn.',
            ],
            'disk' => [
                'label' => 'Dung lượng ổ đĩa',
                'helper' => 'Đặt 0 để cho phép sử dụng đĩa không giới hạn.',
            ],
            'io' => [
                'label' => 'Chặn trọng lượng IO',
                'helper' => 'Nâng cao: Hiệu suất IO so với các vùng chứa đang chạy khác. Giá trị phải từ 10 đến 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Kích hoạt OOM Killer',
                'helper' => 'Chấm dứt máy chủ nếu nó vi phạm giới hạn bộ nhớ.',
            ],
            'nest' => [
                'label' => 'Tổ',
                'helper' => 'Chọn Nest mà máy chủ này sẽ được nhóm lại.',
            ],
            'egg' => [
                'label' => 'Trứng',
                'helper' => 'Chọn Quả trứng sẽ xác định cách thức hoạt động của máy chủ này.',
            ],
            'skip_scripts' => [
                'label' => 'Bỏ qua tập lệnh cài đặt trứng',
                'helper' => 'Nếu Trứng được chọn có tập lệnh cài đặt được đính kèm, tập lệnh sẽ chạy trong khi cài đặt trừ khi điều này được chọn.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Chọn một hình ảnh từ danh sách thả xuống hoặc nhập hình ảnh tùy chỉnh bên dưới.',
            ],
            'custom_image' => [
                'label' => 'Hình ảnh Docker tùy chỉnh',
                'placeholder' => 'Hoặc nhập hình ảnh tùy chỉnh...',
                'helper' => 'Đây là hình ảnh Docker mặc định sẽ được sử dụng để chạy máy chủ này.',
            ],
            'startup' => [
                'label' => 'Lệnh khởi động',
                'helper' => 'Các sản phẩm thay thế có sẵn: {{SERVER_MEMORY}}, {{SERVER_IP}} và {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Chọn một quả trứng để định cấu hình các biến dịch vụ',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Chế độ nâng cao',
            'helper' => 'Chuyển đổi để hiển thị các tùy chọn cấu hình máy chủ bổ sung. Chỉ bật nếu bạn hiểu ý nghĩa của các cài đặt bổ sung.',
        ],
        'external_id' => [
            'label' => 'ID bên ngoài',
            'helper' => 'Mã định danh duy nhất tùy chọn cho máy chủ này.',
        ],
        'owner' => [
            'label' => 'Người sở hữu',
            'helper' => 'Chọn người dùng sở hữu máy chủ này.',
        ],
        'name' => [
            'label' => 'Tên',
            'placeholder' => 'Tên máy chủ',
            'helper' => 'Một tên viết tắt cho máy chủ này.',
        ],
        'description' => [
            'label' => 'Sự miêu tả',
            'placeholder' => 'Mô tả máy chủ',
            'helper' => 'Mô tả tùy chọn cho máy chủ này.',
        ],
        'node' => [
            'label' => 'Nút',
            'helper' => 'Nút mà máy chủ này sẽ được triển khai tới.',
        ],
        'allocation' => [
            'label' => 'Phân bổ chính',
            'helper' => 'Phân bổ IP/cổng mặc định cho máy chủ này.',
        ],
        'additional_allocations' => [
            'label' => 'Phân bổ bổ sung',
            'helper' => 'Phân bổ bổ sung tùy chọn để chỉ định.',
        ],
        'nest' => [
            'label' => 'Tổ',
            'helper' => 'Tổ dịch vụ cho máy chủ này.',
        ],
        'egg' => [
            'label' => 'Trứng',
            'helper' => 'Quả trứng xác định hành vi của máy chủ.',
        ],
        'startup' => [
            'label' => 'Lệnh khởi động',
            'helper' => 'Lệnh khởi động cho máy chủ.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'helper' => 'Hình ảnh Docker được sử dụng để chạy máy chủ này.',
            'custom' => 'Phong tục',
        ],
        'skip_scripts' => [
            'label' => 'Bỏ qua tập lệnh trứng',
            'helper' => 'Bỏ qua các tập lệnh cài đặt trứng trong quá trình tạo.',
        ],
        'start_on_completion' => [
            'label' => 'Bắt đầu khi hoàn thành',
            'helper' => 'Tự động khởi động máy chủ sau khi cài đặt.',
        ],
        'memory' => [
            'label' => 'Ký ức',
            'helper' => 'Tổng phân bổ bộ nhớ. Đặt thành 0 để không giới hạn. (Bộ nhớ không giới hạn không hoạt động đối với Minecraft Eggs do Lệnh khởi động)',
        ],
        'swap' => [
            'label' => 'Tráo đổi',
            'helper' => 'Trao đổi phân bổ bộ nhớ. Đặt thành 0 để tắt trao đổi hoặc -1 để cho phép trao đổi không giới hạn.',
        ],
        'disk' => [
            'label' => 'đĩa',
            'helper' => 'Phân bổ không gian đĩa. Đặt thành 0 để không giới hạn.',
        ],
        'io' => [
            'label' => 'Trọng lượng IO',
            'helper' => 'Ưu tiên I/O đĩa tương đối (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Giới hạn CPU tính bằng phần trăm. 100% có nghĩa là một lõi đầy đủ, 200% có nghĩa là hai lõi đầy đủ, v.v.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Nhập kích thước trong GiB',
            'helper' => 'Bạn có thể nhập kích thước theo GiB bằng cách sử dụng hậu tố "GiB" (ví dụ: 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Chủ đề CPU',
            'helper' => 'Ghim chủ đề tùy chọn. Ví dụ: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Vô hiệu hóa OOM Killer',
            'helper' => 'Ngăn chặn kernel giết tiến trình khi hết bộ nhớ.',
        ],
        'database_limit' => [
            'label' => 'Giới hạn cơ sở dữ liệu',
            'helper' => 'Số lượng cơ sở dữ liệu tối đa',
        ],
        'allocation_limit' => [
            'label' => 'Giới hạn phân bổ',
            'helper' => 'Số lượng phân bổ tối đa.',
        ],
        'backup_limit' => [
            'label' => 'Giới hạn dự phòng',
            'helper' => 'Số lượng bản sao lưu tối đa.',
        ],
        'environment' => [
            'key' => 'Biến',
            'value' => 'Giá trị',
            'helper' => 'Biến môi trường cho quả trứng này.',
        ],
        'use_custom_image' => [
            'label' => 'Sử dụng hình ảnh tùy chỉnh',
            'helper' => 'Chuyển đổi để sử dụng hình ảnh Docker tùy chỉnh thay vì hình ảnh do quả trứng cung cấp.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Tên',
        'owner' => 'Người sở hữu',
        'node' => 'Nút',
        'allocation' => 'Phân bổ',
        'status' => 'Trạng thái',
        'egg' => 'Trứng',
        'memory' => 'Ký ức',
        'disk' => 'đĩa',
        'cpu' => 'CPU',
        'created' => 'Tạo',
        'updated' => 'Đã cập nhật',
        'installed' => 'Đã cài đặt',
        'no_status' => 'Không có trạng thái',
        'unlimited' => 'Không giới hạn',
    ],

    'messages' => [
        'created' => 'Máy chủ đã được tạo thành công.',
        'updated' => 'Máy chủ đã được cập nhật thành công.',
        'deleted' => 'Máy chủ đã được xóa thành công.',
    ],

    'actions' => [
        'edit' => 'Biên tập',
        'random' => 'Ngẫu nhiên',
        'toggle_install_status' => 'Chuyển đổi trạng thái cài đặt',
        'suspend' => 'Đình chỉ',
        'unsuspend' => 'Hủy tạm dừng',
        'suspended' => 'Cấm',
        'unsuspended' => 'Không bị treo',
        'reinstall' => 'Cài đặt lại',
        'delete' => 'Xóa bỏ',
        'delete_forcibly' => 'Buộc xóa',
        'view' => 'Xem',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Bạn đang cố gắng xóa phân bổ mặc định cho máy chủ này nhưng không có phân bổ dự phòng nào để sử dụng.',
        'marked_as_failed' => 'Máy chủ này được đánh dấu là đã cài đặt trước đó không thành công. Trạng thái hiện tại không thể được chuyển đổi ở trạng thái này.',
        'bad_variable' => 'Đã xảy ra lỗi xác thực với biến :name.',
        'daemon_exception' => 'Đã xảy ra ngoại lệ khi cố gắng giao tiếp với daemon dẫn đến mã phản hồi HTTP/:code. Ngoại lệ này đã được ghi lại. (id yêu cầu: :request_id)',
        'default_allocation_not_found' => 'Không tìm thấy phân bổ mặc định được yêu cầu trong phân bổ của máy chủ này.',
    ],

    'alerts' => [
        'install_toggled' => 'Trạng thái cài đặt máy chủ đã được chuyển đổi.',
        'server_suspended' => 'Máy chủ đã được :action.',
        'server_reinstalled' => 'Quá trình cài đặt lại máy chủ đã được bắt đầu.',
        'server_deleted' => 'Máy chủ đã bị xóa.',
        'server_delete_failed' => 'Không xóa được máy chủ.',
        'startup_changed' => 'Cấu hình khởi động cho máy chủ này đã được cập nhật. Nếu tổ hoặc trứng của máy chủ này đã bị thay đổi thì quá trình cài đặt lại sẽ diễn ra ngay bây giờ.',
        'server_created' => 'Máy chủ đã được tạo thành công trên bảng điều khiển. Vui lòng cho phép daemon vài phút để cài đặt hoàn toàn máy chủ này.',
        'build_updated' => 'Chi tiết bản dựng cho máy chủ này đã được cập nhật. Một số thay đổi có thể yêu cầu khởi động lại để có hiệu lực.',
        'suspension_toggled' => 'Trạng thái tạm dừng máy chủ đã được thay đổi thành :status.',
        'rebuild_on_boot' => 'Máy chủ này đã được đánh dấu là yêu cầu xây dựng lại Docker Container. Điều này sẽ xảy ra vào lần tiếp theo khi máy chủ được khởi động.',
        'details_updated' => 'Chi tiết máy chủ đã được cập nhật thành công.',
        'docker_image_updated' => 'Đã thay đổi thành công hình ảnh Docker mặc định để sử dụng cho máy chủ này. Cần phải khởi động lại để áp dụng thay đổi này.',
        'node_required' => 'Bạn phải cấu hình ít nhất một nút trước khi có thể thêm máy chủ vào bảng này.',
        'transfer_nodes_required' => 'Bạn phải cấu hình ít nhất hai nút trước khi có thể chuyển máy chủ.',
        'transfer_started' => 'Quá trình chuyển máy chủ đã được bắt đầu.',
        'transfer_not_viable' => 'Nút bạn đã chọn không có đủ dung lượng ổ đĩa hoặc bộ nhớ cần thiết để chứa máy chủ này.',
        'primary_allocation_updated' => 'Đã cập nhật phân bổ chính.',
        'database_created' => 'Cơ sở dữ liệu được tạo.',
        'database_password_reset' => 'Đặt lại mật khẩu cơ sở dữ liệu.',
        'database_deleted' => 'Cơ sở dữ liệu đã bị xóa.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Thông tin',
            'build_configuration' => 'Xây dựng cấu hình',
            'startup' => 'Khởi động',
            'manage' => 'Quản lý',
        ],

        'sections' => [
            'resource_management' => 'Quản lý tài nguyên',
            'application_feature_limits' => 'Giới hạn tính năng ứng dụng',
            'allocation_management' => 'Quản lý phân bổ',
            'startup_command_modification' => 'Sửa đổi lệnh khởi động',
            'service_configuration' => 'Cấu hình dịch vụ',
            'docker_image_configuration' => 'Cấu hình hình ảnh Docker',
            'service_variables' => 'Biến dịch vụ',
            'reinstall_server' => 'Cài đặt lại máy chủ',
            'install_status' => 'Trạng thái cài đặt',
            'suspend_server' => 'Đình chỉ máy chủ',
            'unsuspend_server' => 'Hủy tạm dừng máy chủ',
            'transfer_server' => 'Chuyển máy chủ',
            'delete_server' => 'Xóa máy chủ',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Việc thay đổi những giá trị này có thể kích hoạt quá trình cài đặt lại. Máy chủ sẽ bị dừng ngay lập tức đối với hoạt động đó.',
            'reinstall_server' => 'Thao tác này sẽ cài đặt lại máy chủ với các tập lệnh dịch vụ được chỉ định. Điều này có thể ghi đè lên dữ liệu máy chủ.',
            'install_status' => 'Thay đổi trạng thái cài đặt từ đã gỡ cài đặt thành đã cài đặt hoặc ngược lại.',
            'suspend_server' => 'Điều này sẽ dừng các tiến trình đang chạy và chặn người dùng quản lý máy chủ thông qua bảng điều khiển hoặc API.',
            'unsuspend_server' => 'Điều này sẽ hủy tạm dừng máy chủ và khôi phục quyền truy cập bình thường của người dùng.',
            'transfer_server_transferring' => 'Máy chủ này hiện đang được chuyển sang một nút khác.',
            'transfer_server' => 'Chuyển máy chủ này sang một nút khác được kết nối với bảng này.',
            'delete_server' => 'Thao tác này sẽ xóa vĩnh viễn máy chủ khỏi bảng điều khiển và Tác nhân. Buộc xóa bỏ qua Xóa tác nhân nếu cần thiết.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Tên máy chủ',
                'helper' => 'Giới hạn ký tự: a-zA-Z0-9_-, dấu cách và ký tự có thể in tiêu chuẩn.',
            ],
            'server_owner' => [
                'label' => 'Chủ sở hữu máy chủ',
                'helper' => 'Việc thay đổi quyền sở hữu sẽ tự động thu hồi mã thông báo daemon cho chủ sở hữu trước đó.',
            ],
            'server_description' => [
                'label' => 'Mô tả máy chủ',
                'helper' => 'Một mô tả ngắn gọn về máy chủ này.',
            ],
            'server_uuid' => [
                'label' => 'UUID máy chủ',
            ],
            'server_uuid_short' => [
                'label' => 'UUID máy chủ (Ngắn)',
            ],
            'external_identifier' => [
                'label' => 'Mã định danh bên ngoài',
                'helper' => 'Để trống để không gán mã định danh bên ngoài. ID bên ngoài phải là duy nhất cho máy chủ này.',
            ],
            'game_port' => [
                'label' => 'Cổng trò chơi',
                'helper' => 'Địa chỉ kết nối mặc định sẽ được sử dụng cho máy chủ trò chơi này.',
            ],
            'additional_ports' => [
                'label' => 'Cổng bổ sung',
                'helper' => 'Gán hoặc loại bỏ các cổng bổ sung. Các cổng giống nhau trên các IP khác nhau không thể được gán cho cùng một máy chủ.',
            ],
            'startup_command' => [
                'label' => 'Lệnh khởi động',
                'helper' => 'Có sẵn theo mặc định: {{SERVER_MEMORY}}, {{SERVER_IP}} và {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Lệnh khởi động mặc định',
                'error' => 'LỖI: Khởi động không được xác định!',
            ],
            'cpu_limit' => [
                'label' => 'Giới hạn CPU',
                'helper' => 'Mỗi lõi ảo là 100%. Đặt 0 cho thời gian CPU không bị giới hạn.',
            ],
            'cpu_pinning' => [
                'label' => 'Ghim CPU',
                'helper' => 'Nâng cao: để trống cho tất cả các lõi. Ví dụ: 0, 0-1,3 hoặc 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Bộ nhớ được phân bổ',
                'helper' => 'Dung lượng bộ nhớ tối đa được phép cho vùng chứa này. Đặt 0 cho không giới hạn.',
            ],
            'allocated_swap' => [
                'label' => 'Hoán đổi được phân bổ',
                'helper' => 'Đặt 0 để tắt trao đổi hoặc -1 để cho phép trao đổi không giới hạn.',
            ],
            'disk_space_limit' => [
                'label' => 'Giới hạn dung lượng ổ đĩa',
                'helper' => 'Đặt 0 để cho phép sử dụng đĩa không giới hạn.',
            ],
            'block_io_proportion' => [
                'label' => 'Chặn tỷ lệ IO',
                'helper' => 'Nâng cao: Hiệu suất IO so với các vùng chứa đang chạy khác. Giá trị phải từ 10 đến 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Vô hiệu hóa OOM Killer',
                'helper' => 'Việc kích hoạt trình diệt OOM có thể khiến các tiến trình máy chủ thoát ra bất ngờ.',
            ],
            'database_limit' => [
                'label' => 'Giới hạn cơ sở dữ liệu',
                'helper' => 'Tổng số cơ sở dữ liệu mà người dùng được phép tạo cho máy chủ này.',
            ],
            'allocation_limit' => [
                'label' => 'Giới hạn phân bổ',
                'helper' => 'Tổng số phân bổ mà người dùng được phép tạo cho máy chủ này.',
            ],
            'backup_limit' => [
                'label' => 'Giới hạn dự phòng',
                'helper' => 'Tổng số bản sao lưu có thể được tạo cho máy chủ này.',
            ],
            'image' => [
                'label' => 'Hình ảnh',
                'helper' => 'Chọn một hình ảnh từ danh sách thả xuống hoặc nhập hình ảnh tùy chỉnh bên dưới.',
            ],
            'custom_image' => [
                'label' => 'Hình ảnh tùy chỉnh',
                'placeholder' => 'Hoặc nhập hình ảnh tùy chỉnh...',
                'helper' => 'Đây là hình ảnh Docker sẽ được sử dụng để chạy máy chủ này.',
            ],
            'transfer_node' => [
                'label' => 'Nút',
                'helper' => 'Nút mà máy chủ này sẽ được chuyển đến.',
            ],
            'transfer_allocation' => [
                'label' => 'Phân bổ mặc định',
                'helper' => 'Sự phân bổ chính sẽ được gán cho máy chủ này.',
            ],
            'transfer_additional_allocations' => [
                'label' => '(Các) Phân bổ bổ sung',
                'helper' => 'Phân bổ bổ sung để gán cho máy chủ này khi chuyển.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Cài đặt lại máy chủ',
            'toggle_install_status' => 'Chuyển đổi trạng thái cài đặt',
            'suspend_server' => 'Đình chỉ máy chủ',
            'unsuspend_server' => 'Hủy tạm dừng máy chủ',
            'transfer_server' => 'Chuyển máy chủ',
            'confirm' => 'Xác nhận',
            'delete_server' => 'Xóa máy chủ',
            'forcibly_delete_server' => 'Buộc xóa máy chủ',
        ],
    ],

    'allocations' => [
        'title' => 'Phân bổ',

        'table' => [
            'ip' => 'IP',
            'port' => 'Cảng',
            'alias' => 'Bí danh',
            'primary' => 'Sơ đẳng',
            'notes' => 'Ghi chú',
            'created' => 'Tạo',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Không có bí danh nào được chỉ định',
        ],

        'actions' => [
            'make_primary' => 'Làm chính',
        ],
    ],

    'databases' => [
        'title' => 'Cơ sở dữ liệu',

        'table' => [
            'database' => 'Cơ sở dữ liệu',
            'username' => 'Tên người dùng',
            'remote' => 'Xa',
            'host' => 'Chủ nhà',
            'max_connections' => 'Kết nối tối đa',
            'created' => 'Tạo',
        ],

        'placeholder' => [
            'unlimited' => 'Không giới hạn',
        ],

        'actions' => [
            'create_database' => 'Tạo cơ sở dữ liệu',
            'reset_password' => 'Đặt lại mật khẩu',
            'delete' => 'Xóa bỏ',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Tên cơ sở dữ liệu',
                'helper' => 'Bảng điều khiển sẽ đặt tiền tố này bằng ID máy chủ, khớp với bảng quản trị cũ.',
            ],
            'database_host' => [
                'label' => 'Máy chủ cơ sở dữ liệu',
            ],
            'remote' => [
                'label' => 'Xa',
            ],
            'max_connections' => [
                'label' => 'Kết nối tối đa',
            ],
        ],
    ],
];
