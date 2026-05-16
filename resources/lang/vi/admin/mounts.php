<?php

return [

    'label' => 'Gắn kết',
    'plural_label' => 'Gắn kết',

    'sections' => [
        'configuration' => 'Cấu hình gắn kết',
    ],

    'fields' => [
        'name' => 'Tên',
        'description' => 'Sự miêu tả',
        'source' => 'Đường dẫn nguồn',
        'target' => 'Đường dẫn đích',
        'read_only' => 'Chỉ đọc',
        'user_mountable' => 'Người dùng có thể gắn kết',
    ],

    'helpers' => [
        'name' => 'Một cái tên duy nhất dùng để phân biệt thú cưỡi này với thú cưỡi khác.',
        'description' => 'Một mô tả dài hơn, dễ đọc của con người về thú cưỡi này.',
        'source' => 'Đường dẫn tệp trên máy chủ để gắn vào vùng chứa.',
        'target' => 'Đường dẫn bên trong container để gắn kết cái này là.',
        'read_only' => 'Nếu được đặt, giá treo sẽ ở chế độ chỉ đọc bên trong vùng chứa.',
        'user_mountable' => 'Nếu được đặt, người dùng sẽ có thể gắn kết này vào máy chủ của họ.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Tên',
        'source' => 'Nguồn',
        'target' => 'Mục tiêu',
        'read_only' => 'Chỉ đọc',
        'user_mountable' => 'Người dùng có thể gắn kết',
    ],

    'actions' => [
        'attach_egg' => 'Gắn trứng',
        'attach_node' => 'Đính kèm nút',
    ],

];
