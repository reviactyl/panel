<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'System User',
        'system' => 'System',
        'using-api-key' => 'Using API Key',
        'using-sftp' => 'Using SFTP',
    ],
    'auth' => [
        'fail' => 'লগইন ব্যর্থ হয়েছে।',
        'success' => 'Logged in',
        'password-reset' => 'Password reset',
        'reset-password' => 'Requested password reset',
        'checkpoint' => 'Two-factor authentication requested',
        'recovery-token' => 'Used two-factor recovery token',
        'token' => 'Solved two-factor challenge',
        'ip-blocked' => 'Blocked request from unlisted IP address for :identifier',
        'sftp' => [
            'fail' => 'Failed SFTP log in',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'Changed email from :old to :new',
            'password-changed' => 'Changed password',
            'language-changed' => 'Changed language from :old to :new',
        ],
        'api-key' => [
            'create' => 'Created new API key :identifier',
            'delete' => 'Deleted API key :identifier',
        ],
        'ssh-key' => [
            'create' => 'Added SSH key :fingerprint to account',
            'delete' => 'Removed SSH key :fingerprint from account',
        ],
        'two-factor' => [
            'create' => 'Enabled two-factor auth',
            'delete' => 'Disabled two-factor auth',
        ],
    ],
    'server' => [
        'reinstall' => 'Reinstalled server',
        'console' => [
            'command' => 'Executed ":command" on the server',
        ],
        'power' => [
            'start' => 'Started the server',
            'stop' => 'Stopped the server',
            'restart' => 'Restarted the server',
            'kill' => 'Killed the server process',
        ],
        'backup' => [
            'download' => 'Downloaded the :name backup',
            'delete' => 'Deleted the :name backup',
            'restore' => 'Restored the :name backup (deleted files: :truncate)',
            'restore-complete' => 'Completed restoration of the :name backup',
            'restore-failed' => 'Failed to complete restoration of the :name backup',
            'start' => 'Started a new backup :name',
            'complete' => 'Marked the :name backup as complete',
            'fail' => 'Marked the :name backup as failed',
            'lock' => 'Locked the :name backup',
            'unlock' => 'Unlocked the :name backup',
        ],
        'database' => [
            'create' => 'Created new database :name',
            'rotate-password' => 'Password rotated for database :name',
            'delete' => 'Deleted database :name',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:file',
            'compress_other' => 'Compressed :count files in :directory',
            'read' => 'Viewed the contents of :file',
            'copy' => 'Created a copy of :file',
            'create-directory' => 'Created directory :directory:name',
            'decompress' => 'Decompressed :files in :directory',
            'delete_one' => 'Deleted :directory:files.0',
            'delete_other' => 'Deleted :count files in :directory',
            'download' => 'Downloaded :file',
            'pull' => 'Downloaded a remote file from :url to :directory',
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
            'delete_other' => 'Deleted :count files',
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
            'create' => 'Created the :name schedule',
            'update' => 'Updated the :name schedule',
            'execute' => 'Manually executed the :name schedule',
            'delete' => 'Deleted the :name schedule',
        ],
        'task' => [
            'create' => 'Created a new ":action" task for the :name schedule',
            'update' => 'Updated the ":action" task for the :name schedule',
            'delete' => 'Deleted a task for the :name schedule',
        ],
        'settings' => [
            'rename' => 'Renamed the server from :old to :new',
            'description' => 'Changed the server description from :old to :new',
        ],
        'startup' => [
            'edit' => 'Changed the :variable variable from ":old" to ":new"',
            'image' => 'Updated the Docker Image for the server from :old to :new',
        ],
        'subuser' => [
            'create' => 'Added :email as a subuser',
            'update' => 'Updated the subuser permissions for :email',
            'delete' => 'Removed :email as a subuser',
        ],
    ],
];
