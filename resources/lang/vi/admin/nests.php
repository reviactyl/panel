<?php

return [

    'label' => 'Tổ',
    'plural_label' => 'Tổ yến',

    'sections' => [
        'configuration' => 'Cấu hình tổ',
    ],

    'fields' => [
        'name' => 'Tên',
        'author' => 'Tác giả',
        'description' => 'Sự miêu tả',
    ],

    'helpers' => [
        'name' => 'Một cái tên duy nhất được sử dụng để xác định tổ này.',
        'author' => 'Tác giả của tổ này. Phải là một email hợp lệ.',
        'description' => 'Một mô tả về tổ này.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Tên',
        'author' => 'Tác giả',
        'eggs' => 'Trứng',
        'servers' => 'Máy chủ',
    ],

    'actions' => [
        'import' => 'Trứng nhập khẩu',
    ],

    'import' => [
        'file_label' => 'Tệp trứng (JSON)',
        'nest_label' => 'Tổ liên kết',
        'file_not_found' => 'Không tìm thấy tập tin',
        'file_not_found_body' => 'Không thể định vị tập tin đã tải lên.',
        'invalid_format' => 'Định dạng tệp không hợp lệ',
        'invalid_format_body' => 'Đã nhận được định dạng tệp không mong muốn.',
        'success' => 'Trứng được nhập thành công',
        'failed' => 'Không thể nhập trứng',
    ],

    'notices' => [
        'created' => 'Một tổ mới, :name, đã được tạo thành công.',
        'deleted' => 'Đã xóa thành công tổ được yêu cầu khỏi Bảng điều khiển.',
        'updated' => 'Đã cập nhật thành công các tùy chọn cấu hình tổ.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Đã nhập thành công Trứng này và các biến liên quan của nó.',
            'updated_via_import' => 'Quả trứng này đã được cập nhật bằng tệp được cung cấp.',
            'deleted' => 'Đã xóa thành công quả trứng được yêu cầu khỏi Bảng điều khiển.',
            'updated' => 'Cấu hình trứng đã được cập nhật thành công.',
            'script_updated' => 'Tập lệnh cài đặt Egg đã được cập nhật và sẽ chạy bất cứ khi nào máy chủ được cài đặt.',
            'egg_created' => 'Một quả trứng mới đã được đẻ thành công. Bạn sẽ cần phải khởi động lại mọi daemon đang chạy để áp dụng quả trứng mới này.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'Biến ":variable" đã bị xóa và sẽ không còn khả dụng trên máy chủ sau khi được xây dựng lại.',
            'variable_updated' => 'Biến ":variable" đã được cập nhật. Bạn sẽ cần phải xây dựng lại bất kỳ máy chủ nào sử dụng biến này để áp dụng các thay đổi.',
            'variable_created' => 'Biến mới đã được tạo và gán thành công cho quả trứng này.',
        ],
    ],
];
