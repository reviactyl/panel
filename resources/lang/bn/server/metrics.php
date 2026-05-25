<?php

return [
    'title' => 'ঐতিহাসিক মেট্রিক্স',
    'error' => 'ঐতিহাসিক পরিসংখ্যান লোড করা যায়নি।',
    'time_range' => [
        'last_24_hours' => 'গত ২৪ ঘণ্টা',
        'last_3_days' => 'গত ৩ দিন',
        'last_7_days' => 'গত ৭ দিন',
    ],
    'charts' => [
        'cpu' => [
            'title' => 'CPU ইতিহাস',
            'label' => 'CPU ব্যবহার (%)',
        ],
        'memory' => [
            'title' => 'মেমোরি ইতিহাস',
            'label' => 'মেমোরি ব্যবহার (MB)',
        ],
        'disk' => [
            'title' => 'ডিস্ক ইতিহাস',
            'label' => 'ডিস্ক ব্যবহার (MB)',
        ],
        'network' => [
            'title' => 'নেটওয়ার্ক ইতিহাস',
            'rx_label' => 'নেটওয়ার্ক RX (MB)',
            'tx_label' => 'নেটওয়ার্ক TX (MB)',
        ],
    ],
];
