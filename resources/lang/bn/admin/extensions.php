<?php

return [

    'label' => 'এক্সটেনশন',
    'plural-label' => 'এক্সটেনশনসমূহ',

    'columns' => [
        'id' => 'আইডি',
        'name' => 'নাম',
        'version' => 'ভার্সন',
        'author' => 'লেখক',
        'enabled' => 'সক্রিয়',
        'updated' => 'আপডেট করা হয়েছে',
        'manifest_json' => 'Manifest JSON',
    ],

    'modals' => [
        'manifest' => 'এক্সটেনশন Manifest',
    ],

    'actions' => [
        'edit' => 'সম্পাদনা',
        'upload' => 'আপলোড',
        'manifest' => 'Manifest দেখুন',
        'disable' => 'নিষ্ক্রিয় করুন',
        'enable' => 'সক্রিয় করুন',
        'delete' => 'মুছে ফেলুন',
        'close' => 'বন্ধ করুন',
    ],

    'alerts' => [
        'enabled' => 'এক্সটেনশন সক্রিয় করা হয়েছে।',
        'enable_failed' => 'এক্সটেনশন সক্রিয় করতে ব্যর্থ হয়েছে।',
        'disabled' => 'এক্সটেনশন নিষ্ক্রিয় করা হয়েছে।',
        'disable_failed' => 'এক্সটেনশন নিষ্ক্রিয় করতে ব্যর্থ হয়েছে।',
        'uninstalled' => 'এক্সটেনশন আনইনস্টল করা হয়েছে।',
        'uninstall_failed' => 'এক্সটেনশন আনইনস্টল করতে ব্যর্থ হয়েছে।',
        'could_not_locate_file' => 'আপলোড করা প্যাকেজ ফাইল খুঁজে পাওয়া যায়নি।',
        'invalid_file_type' => 'শুধুমাত্র .rext ফাইল অনুমোদিত।',
        'upload_hint' => 'শুধুমাত্র .rext এক্সটেনশন প্যাকেজ অনুমোদিত।',
        'install_failed' => 'এক্সটেনশন ইনস্টল ব্যর্থ হয়েছে।',
        'install_success' => ':name (:version) সফলভাবে ইনস্টল করা হয়েছে।',
    ],

];
