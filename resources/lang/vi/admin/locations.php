<?php

return [

    'label' => 'Vị trí',
    'plural-label' => 'Địa điểm',

    'section' => [
        'title' => 'Chi tiết vị trí',
        'description' => 'Xác định vị trí mà các nút có thể được chỉ định.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Mã ngắn',
            'placeholder' => 'chúng tôi.nyc.1',
            'helper' => 'Mã định danh ngắn cho vị trí này.',
        ],

        'long' => [
            'label' => 'Sự miêu tả',
            'placeholder' => 'Thành phố New York, NY, Hoa Kỳ',
            'helper' => 'Mô tả dài hơn về vị trí này.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'short' => 'Mã ngắn',
        'long' => 'Sự miêu tả',
        'nodes' => 'Nút',
        'servers' => 'Máy chủ',
        'created' => 'Tạo',
    ],

    'actions' => [
        'edit' => 'Biên tập',
        'delete' => 'Xóa bỏ',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'Không thể xóa vị trí có các nút được liên kết.',
    ],

];
