<?php

return [
    'navigation' => [
        'label' => 'মনিটরিং',
        'group' => 'প্রশাসন',
    ],

    'page' => [
        'title' => 'মনিটরিং',
        'heading' => 'লাইভ মনিটরিং',
    ],

    'actions' => [
        'refresh' => 'ডাটা রিফ্রেশ করুন',
    ],

    'selector' => [
        'label' => 'নোড নির্বাচন করুন',
        'placeholder' => 'একটি নোড নির্বাচন করুন...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU ব্যবহার',
        'cpu_cores' => ':count টি কোর উপলব্ধ',
        'memory_usage' => 'মেমোরি ব্যবহার',
        'disk_usage' => 'ডিস্ক ব্যবহার',
        'network_traffic' => 'নেটওয়ার্ক ট্রাফিক',
        'uptime' => 'চালু থাকার সময়',
        'last_updated' => 'সর্বশেষ আপডেট',
        'no_node' => 'কোনো নোড নির্বাচন করা হয়নি',
        'no_node_desc' => 'মনিটরিং ডাটা দেখতে অনুগ্রহ করে একটি নোড নির্বাচন করুন',
        'no_node_hint' => 'উপরের ড্রপডাউন ব্যবহার করুন',
        'error' => 'ত্রুটি',
        'error_desc' => 'মনিটরিং ডাটা লোড করা যায়নি',
        'error_fetch' => 'Agent থেকে ডাটা আনা যায়নি',
        'error_node_gone' => 'নোড আর বিদ্যমান নেই',
    ],

    'details' => [
        'heading' => 'সিস্টেমের বিবরণ',
        'button' => 'বিবরণ',
        'close' => 'বন্ধ করুন',
        'no_data' => 'কোনো ডাটা উপলব্ধ নেই। নিশ্চিত করুন যে নোড অনলাইনে রয়েছে।',

        'cpu_section' => 'সিপিইউ',
        'cpu_total' => 'মোট ব্যবহার',
        'cpu_cores' => 'কোরসমূহ',
        'per_core' => 'প্রতি-কোর ব্যবহার',

        'memory_section' => 'মেমোরি',
        'total_memory' => 'মোট',
        'used_memory' => 'ব্যবহৃত',
        'free_memory' => 'ফাঁকা',
        'available_memory' => 'উপলব্ধ',

        'swap_section' => 'সোয়াপ',
        'swap_none' => 'এই নোডে কোনো swap কনফিগার করা নেই।',
        'swap_total' => 'মোট',
        'swap_used' => 'ব্যবহৃত',
        'swap_free' => 'ফাঁকা',
        'swap_usage' => 'ব্যবহার',

        'network_section' => 'নেটওয়ার্ক',
        'bytes_sent' => 'পাঠানো বাইট',
        'bytes_recv' => 'গৃহীত বাইট',
        'packets_sent' => 'পাঠানো প্যাকেট',
        'packets_received' => 'গৃহীত প্যাকেট',

        'runtime_section' => 'রানটাইম',
        'go_version' => 'Go ভার্সন',
        'arch' => 'আর্কিটেকচার',
        'goroutines' => 'গরুটিনস',
        'uptime' => 'চালু থাকার সময়',
    ],
    'servers' => [
        'heading' => 'সার্ভার ব্যবহার',
        'no_node' => 'সার্ভার ব্যবহার দেখতে একটি নোড নির্বাচন করুন।',
        'no_servers' => 'এই নোডে কোনো সার্ভার পাওয়া যায়নি।',
        'error_fetch' => 'Agent থেকে সার্ভারের ডাটা আনা যায়নি।',
        'col' => [
            'name' => 'সার্ভার',
            'state' => 'অবস্থা',
            'cpu' => 'সিপিইউ',
            'memory' => 'মেমোরি',
            'disk' => 'ডিস্ক',
            'network' => 'নেটওয়ার্ক',
            'uptime' => 'চালু থাকার সময়',
        ],
        'states' => [
            'running' => 'চলমান',
            'starting' => 'চালু হচ্ছে',
            'stopping' => 'বন্ধ হচ্ছে',
            'offline' => 'অফলাইন',
            'crashed' => 'ক্র্যাশ হয়েছে',
            'unknown' => 'অজানা',
        ],
    ],
];
