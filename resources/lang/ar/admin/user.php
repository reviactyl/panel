<?php

return [
    'title' => 'User',
    'exceptions' => [
        'delete_self' => 'You cannot delete your own account.',
        'user_has_servers' => 'Cannot delete a user with active servers attached to their account. Please delete their servers before continuing.',
    ],
    'notices' => [
        'account_created' => 'Account has been created successfully.',
        'account_updated' => 'Account has been successfully updated.',
    ],
    'details' => [
        'account_details' => 'Account Details',
        'external_id' => 'External ID',
        'username' => 'Username',
        'email' => 'Email Address',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'language' => 'Language',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'root_admin' => 'Root Administrator',
        'root_admin_desc' => 'This user will have full access to all servers and settings on the system.',
        'privileges' => 'Privileges',
        'admin_status' => 'Admin Status',
    ],
];
