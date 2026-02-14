<?php

return [
    'title' => 'Settings',
    'sftp' => [
        'title' => 'SFTP Details',
        'address' => 'Server Address',
        'username' => 'Username',
        'password' => 'Your SFTP password is the same as the password you use to access this panel.',
        'button' => 'Launch SFTP',
    ],
    'info' => [
        'title' => 'Debug Information',
        'node' => 'Node',
        'server' => 'Server ID',
    ],
    'rename' => [
        'title' => 'Change Server Details',
        'name' => 'Server Name',
        'description' => 'Server Description',
        'button' => 'Save',
    ],
    'reinstall' => [
        'title' => 'Reinstall Server',
        'confirm-title' => 'Confirm server reinstallation',
        'confirm' => 'Yes, reinstall server',
        'info' => 'Your server will be stopped and some files may be deleted or modified during this process, are you sure you wish to continue?',
        'info-1' => 'Reinstalling your server will stop it, and then re-run the installation script that initially set it up.',
        'info-2' => 'Some files may be deleted or modified during this process, please back up your data before continuing.',
        'button' => 'Reinstall Server',
    ],
];
