<?php

return [

    'tabs' => [
        'configuration' => '',
    ],

    'sections' => [
        'configuration' => [
            'title' => '',
        ],
        'identity' => [
            'title' => 'পরিচিতি',
        ],
        'docker_images' => [
            'title' => 'Docker ইমেজসমূহ',
            'description' => 'এই egg ব্যবহারকারী সার্ভারগুলোর জন্য উপলব্ধ Docker ইমেজসমূহ। প্রতি লাইনে একটি করে লিখুন।',
        ],
        'process_management' => [
            'title' => 'প্রসেস ব্যবস্থাপনা',
        ],
        'variables' => [
            'title' => 'ভেরিয়েবলসমূহ',
        ],
        'install_script' => [
            'title' => 'ইনস্টল স্ক্রিপ্ট',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'name' => 'নাম',
        'author' => 'লেখক',
        'image' => 'ইমেজ',
        'description' => 'বিবরণ',
        'image_name' => 'ইমেজের নাম',
        'image_uri' => 'ইমেজ URI',
        'add_docker_image' => 'Docker ইমেজ যোগ করুন',
        'force_outgoing_ip' => 'আউটগোয়িং IP বাধ্যতামূলক করুন',
        'features' => 'ফিচারসমূহ',
        'startup' => 'স্টার্টআপ কমান্ড',
        'config_stop' => 'স্টপ কমান্ড',
        'config_from' => 'সেটিংস কপি করুন',
        'config_startup' => 'স্টার্ট কনফিগারেশন (JSON)',
        'config_logs' => 'লগ কনফিগারেশন (JSON)',
        'config_files' => 'কনফিগারেশন ফাইলসমূহ (JSON)',
        'file_denylist' => 'ফাইল নিষিদ্ধ তালিকা',
        'env_variable' => 'এনভায়রনমেন্ট ভেরিয়েবল',
        'user_viewable' => 'ইউজার দেখতে পারবে',
        'user_editable' => 'ইউজার সম্পাদনা করতে পারবে',
        'rules' => 'ইনপুট নিয়ম',
        'default_value' => 'ডিফল্ট মান',
        'script_install' => 'ইনস্টল স্ক্রিপ্ট',
        'script_container' => 'স্ক্রিপ্ট কনটেইনার',
        'script_entry' => 'স্ক্রিপ্ট এন্ট্রিপয়েন্ট কমান্ড',
        'copy_script_from' => 'যেখান থেকে স্ক্রিপ্ট কপি করবেন',
        'script_is_privileged' => 'প্রিভিলেজড',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'সব আউটগোয়িং নেটওয়ার্ক ট্রাফিককে সার্ভারের প্রধান অ্যালোকেশন IP এর Source IP NAT ব্যবহার করতে বাধ্য করে।',
        'features' => 'Egg-এর অতিরিক্ত ফিচারসমূহ। অতিরিক্ত প্যানেল পরিবর্তন কনফিগার করতে উপকারী।',
        'file_denylist' => 'যে ফাইলগুলো ইউজার সম্পাদনা করতে পারবে না।',
        'script_is_privileged' => 'ইনস্টল স্ক্রিপ্ট প্রিভিলেজড কনটেইনার (root) হিসেবে চালান।',
    ],

    'actions' => [
        'export' => 'এক্সপোর্ট',
        'create' => 'Egg তৈরি করুন',
        'edit' => 'সম্পাদনা',
    ],

    'notices' => [
        'cannot_delete' => 'Egg মুছে ফেলা যাবে না',
        'cannot_delete_body' => 'এই egg এর সাথে :count টি সার্ভার যুক্ত রয়েছে। অনুগ্রহ করে আগে সেগুলো মুছে ফেলুন বা পুনরায় বরাদ্দ করুন।',
        'cannot_delete_multiple' => 'সার্ভারযুক্ত egg মুছে ফেলা যাবে না',
        'cannot_delete_multiple_body' => ':count টি egg এর সাথে সার্ভার যুক্ত থাকায় সেগুলো বাদ দেওয়া হয়েছে।',
    ],

];
