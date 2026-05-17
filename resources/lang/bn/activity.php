<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'সিস্টেম ব্যবহারকারী',
        'system' => 'সিস্টেম',
        'using-api-key' => 'এপিআই কি ব্যবহার করে',
        'using-sftp' => 'SFTP ব্যবহার করে',
    ],
    'auth' => [
        'fail' => 'লগইন ব্যর্থ হয়েছে।',
        'success' => 'লগইন হয়েছে।',
        'password-reset' => 'পাসওয়ার্ড রিসেট।',
        'reset-password' => 'পাসওয়ার্ড রিসেট অনুরোধ করা হয়েছে।',
        'checkpoint' => 'টু-ফ্যাক্টর অথেন্টিকেশন অনুরোধ করা হয়েছে।',
        'recovery-token' => 'টু-ফ্যাক্টর রিকভারি টোকেন ব্যবহার করা হয়েছে।',
        'token' => 'টু-ফ্যাক্টর চ্যালেঞ্জ সম্পন্ন হয়েছে।',
        'ip-blocked' => 'আনলিস্টেড অ্যাড্রেস থেকে :identifier-এর রিকোয়েস্টটি ব্লক করা হয়েছে।',
        'sftp' => [
            'fail' => 'SFTP লগইন ব্যর্থ হয়েছে।',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => ':old ইমেইলটি পরিবর্তন করে :new করা হয়েছে।',
            'password-changed' => 'পাসওয়ার্ড পরিবর্তন করা হয়েছে।',
            'language-changed' => 'ভাষা :old থেকে পরিবর্তন করে :new করা হয়েছে।',
        ],
        'api-key' => [
            'create' => 'নতুন এপিআই কি :identifier তৈরি করা হয়েছে।',
            'delete' => 'এপিআই কি :identifier ডিলিট হয়েছে।',
        ],
        'ssh-key' => [
            'create' => 'অ্যাকাউন্টে ssh কি :fingerprint যুক্ত করা হয়েছে।',
            'delete' => 'অ্যাকাউন্ট থেকে ssh কি :fingerprint রিমুভ করা হয়েছে।',
        ],
        'two-factor' => [
            'create' => 'টু-ফ্যাক্টর অথেন্টিকেশন চালু করা হয়েছে।',
            'delete' => 'টু-ফ্যাক্টর অথেন্টিকেশন বন্ধ করা হয়েছে।',
        ],
    ],
    'server' => [
        'reinstall' => 'সার্ভার রি-ইনস্টল করা হয়েছে।',
        'console' => [
            'command' => 'সার্ভারে ":command" কমান্ডটি চালানো হয়েছে।',
        ],
        'power' => [
            'start' => 'সার্ভারটি চালু করা হয়েছে।',
            'stop' => 'সার্ভারটি বন্ধ করা হয়েছে।',
            'restart' => 'সার্ভারটি রিস্টার্ট করা হয়েছে।',
            'kill' => 'সার্ভার প্রসেসটি জোরপূর্বক বন্ধ করা হয়েছে।',
        ],
        'backup' => [
            'download' => ':name ব্যাকআপটি ডাউনলোড করা হয়েছে।',
            'delete' => ':name ব্যাকআপটি ডিলিট করা হয়েছে।',
            'restore' => ':name ব্যাকআপটি পূর্বাবস্থায় ফিরিয়ে আনা হয়েছে (ডিলিট হওয়া ফাইল: :truncate)।',
            'restore-complete' => 'Name ব্যাকআপটি পূর্বাবস্থায় ফিরিয়ে আনার কাজ সফলভাবে শেষ হয়েছে।',
            'restore-failed' => ':name ব্যাকআপটি পূর্বাবস্থায় ফিরিয়ে আনার কাজ সফলভাবে শেষ করা যায়নি।',
            'start' => 'একটি নতুন ব্যাকআপ :name শুরু করা হয়েছে।',
            'complete' => ':name ব্যাকআপটিকে সম্পন্ন হিসেবে চিহ্নিত করা হয়েছে।',
            'fail' => ':name ব্যাকআপটিকে ব্যর্থ হিসেবে চিহ্নিত করা হয়েছে।',
            'lock' => ':name ব্যাকআপটি সুরক্ষিত করা হয়েছে।',
            'unlock' => ':name ব্যাকআপটির লক খোলা হয়েছে।',
        ],
        'database' => [
            'create' => 'একটি নতুন ডেটাবেস :name তৈরি করা হয়েছে।',
            'rotate-password' => ':name ডেটাবেসের পাসওয়ার্ড পরিবর্তন/রোটেশন করা হয়েছে।',
            'delete' => ':name ডেটাবেসটি ডিলিট করা হয়েছে।',
        ],
        'file' => [
            'compress_one' => ':directory ডিরেক্টরির :file ফাইলটি কমপ্রেস করা হয়েছে।',
            'compress_other' => ':directory ডিরেক্টরির :count টি ফাইল কমপ্রেস করা হয়েছে।',
            'read' => ':file ফাইলের ভেতরের বিষয়বস্তু দেখা হয়েছে।',
            'copy' => ':file ফাইলের একটি কপি তৈরি করা হয়েছে।',
            'create-directory' => ':directory ডিরেক্টরিতে :name নামের একটি নতুন ফোল্ডার/ডিরেক্টরি তৈরি করা হয়েছে।',
            'decompress' => ':directory ডিরেক্টরির :files ফাইলগুলো ডিকমপ্রেস করা হয়েছে।',
            'delete_one' => ':directory ডিরেক্টরির :files.0 ফাইলটি মুছে ফেলা হয়েছে।',
            'delete_other' => ':directory ডিরেক্টরির :count টি ফাইল মুছে ফেলা হয়েছে।',
            'download' => ':file ফাইলটি ডাউনলোড করা হয়েছে।',
            'pull' => 'একটি রিমোট ফাইল :url থেকে :directory ডিরেক্টরিতে ডাউনলোড করা হয়েছে।',
            'rename_one' => ':directory ডিরেক্টরির :files.0.from ফাইলটির নাম পরিবর্তন করে :files.0.to করা হয়েছে।',
            'rename_other' => ':directory ডিরেক্টরির :count টি ফাইলের নাম পরিবর্তন করা হয়েছে।',
            'write' => ':file ফাইলে নতুন কনটেন্ট/তথ্য লেখা হয়েছে।',
            'upload' => 'একটি ফাইল আপলোড শুরু করা হয়েছে।',
            'uploaded' => ':directory ডিরেক্টরির :file ফাইলটি আপলোড করা হয়েছে।',
        ],
        'sftp' => [
            'denied' => 'পারমিশন (অনুমতি) সমস্যার কারণে SFTP অ্যাক্সেস ব্লক করা হয়েছে।',
            'create_one' => ':files.0 তৈরি করা হয়েছে।',
            'create_other' => ':count টি নতুন ফাইল তৈরি করা হয়েছে।',
            'write_one' => ':files.0 ফাইলের বিষয়বস্তু পরিবর্তন করা হয়েছে।',
            'write_other' => ':count টি ফাইলের বিষয়বস্তু পরিবর্তন করা হয়েছে।',
            'delete_one' => ':files.0 মুছে ফেলা হয়েছে।',
            'delete_other' => ':count টি ফাইল মুছে ফেলা হয়েছে।',
            'create-directory_one' => ':files.0 নামের ডিরেক্টরি/ফোল্ডারটি তৈরি করা হয়েছে।',
            'create-directory_other' => ':count টি ডিরেক্টরি/ফোল্ডার তৈরি করা হয়েছে।',
            'rename_one' => ':files.0.from এর নাম পরিবর্তন করে :files.0.to করা হয়েছে।',
            'rename_other' => ':count টি ফাইল নাম পরিবর্তন বা স্থানান্তর করা হয়েছে।',
        ],
        'allocation' => [
            'create' => 'সার্ভারে :allocation যুক্ত করা হয়েছে।',
            'notes' => ':allocation এর নোটটি ":old" থেকে ":new" এ আপডেট করা হয়েছে।',
            'primary' => ':allocation-কে সার্ভারের প্রাইমারি অ্যালোকেশন হিসেবে সেট করা হয়েছে।',
            'delete' => ':allocation অ্যালোকেশনটি মুছে ফেলা হয়েছে।',
        ],
        'schedule' => [
            'create' => ':name শিডিউলটি তৈরি করা হয়েছে।',
            'update' => ':name শিডিউল টি আপডেট করা হয়েছে।',
            'execute' => 'ম্যানুয়ালি :name শিডিউলটি চালানো হয়েছে।',
            'delete' => ':name শিডিউল টি ডিলিট করা হয়েছে।',
        ],
        'task' => [
            'create' => 'নতুন :action কাজটি :name শিডিউলের জন্য তৈরি করা হয়েছে।',
            'update' => '":action" কাজটি :name শিডিউলের জন্য আপডেট করা হয়েছে।',
            'delete' => 'কাজটি :name শিডিউলের জন্য ডিলিট করা হয়েছে।',
        ],
        'settings' => [
            'rename' => 'সার্ভার টির নাম :old থেকে নতুনে :new এ পরিবর্তন করা হয়েছে।',
            'description' => 'সার্ভার টির ডেসক্রিপশন :old থেকে :new এ পরিবর্তন করা হয়েছে।',
        ],
        'startup' => [
            'edit' => ':variable ভেরিএবল টি ":old" থেকে ":new" এ পরিবর্তন করা হয়েছে।',
            'image' => 'সার্ভারের ডকার ইমেজ :old থেকে :new-এ আপডেট করা হয়েছে।',
        ],
        'subuser' => [
            'create' => ':email-কে সাব-ইউজার হিসেবে যোগ করা হয়েছে',
            'update' => ':email-এর জন্য সাব-ইউজার পারমিশন আপডেট করা হয়েছে।',
            'delete' => 'সাব-ইউজার থেকে :email-কে সরিয়ে দেওয়া হয়েছে।',
        ],
    ],
];
