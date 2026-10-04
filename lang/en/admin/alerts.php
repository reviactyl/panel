<?php

return [

    'label' => 'Alert',
    'plural_label' => 'Alerts',

    'sections' => [
        'content' => 'Content',
        'content_description' => 'What the alert says and how it looks.',
        'buttons' => 'Call to Action',
        'buttons_description' => 'Add up to three buttons that send people somewhere.',
        'display' => 'Display',
        'display_description' => 'Where the alert appears and whether people can close it.',
        'schedule' => 'Schedule',
        'schedule_description' => 'Leave both empty to show the alert until it is disabled.',
        'audience' => 'Audience',
        'audience_description' => 'Every rule you set must match for someone to see the alert. Nobody is signed in on the sign-in pages, so an alert with any rule set here or under Servers is never shown there.',
        'servers' => 'Servers',
        'servers_description' => 'Target people by the servers they can access. On a server page the rules are checked against the server being viewed.',
    ],

    'fields' => [
        'name' => 'Name',
        'type' => 'Type',
        'title' => 'Title',
        'message' => 'Message',
        'color' => 'Accent Color',
        'buttons' => 'Buttons',
        'button_label' => 'Label',
        'button_url' => 'Link',
        'button_style' => 'Style',
        'button_new_tab' => 'Open in a new tab',
        'dismiss_on_action' => 'Hide after a button is clicked',
        'enabled' => 'Enabled',
        'placements' => 'Show On',
        'priority' => 'Priority',
        'dismissible' => 'Dismissible',
        'redisplay_after_days' => 'Show Again After',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
        'audience' => 'Who Can See It',
        'users' => 'Specific Users',
        'languages' => 'Languages',
        'two_factor' => 'Two-Factor Authentication',
        'account_age_min' => 'Minimum Account Age',
        'account_age_max' => 'Maximum Account Age',
        'servers' => 'Server Access',
        'nodes' => 'Nodes',
        'locations' => 'Locations',
        'nests' => 'Nests',
        'eggs' => 'Eggs',
    ],

    'helpers' => [
        'name' => 'Only shown in the admin area to tell alerts apart.',
        'title' => 'An optional bold heading shown before the message.',
        'message' => 'Supports **bold** text and [links](https://example.com).',
        'color' => 'Overrides the color that comes with the type.',
        'button_url' => 'A full URL, or a path on this panel such as /account.',
        'dismiss_on_action' => 'The alert goes away for someone once they follow one of its buttons.',
        'enabled' => 'Disabled alerts are kept but never shown.',
        'placements' => 'The parts of the panel this alert is displayed in.',
        'priority' => 'Alerts with a higher priority are shown first.',
        'dismissible' => 'Lets people close the alert. It stays closed on every device they use. Turning this off brings the alert back for everyone who closed it.',
        'redisplay_after_days' => 'Bring the alert back this many days after it was closed. Leave empty to keep it closed.',
        'starts_at' => 'Times use the panel timezone (:timezone).',
        'ends_at' => 'The alert stops showing at this time.',
        'users' => 'Leave empty to not limit the alert to particular people.',
        'languages' => 'Only people using one of these languages will see the alert.',
        'account_age' => 'In days since the account was created.',
        'server_filters' => 'Leave empty to match any. Selecting something here requires a matching server.',
    ],

    'types' => [
        'info' => 'Info',
        'announcement' => 'Announcement',
        'success' => 'Success',
        'warning' => 'Warning',
        'danger' => 'Danger',
    ],

    'placements' => [
        'dashboard' => 'Dashboard',
        'server' => 'Server pages',
        'account' => 'Account pages',
        'auth' => 'Sign-in pages',
    ],

    'audiences' => [
        'everyone' => 'Everyone',
        'admins' => 'Administrators only',
        'users' => 'Regular users only',
    ],

    'two_factor' => [
        'any' => 'Any',
        'enabled' => 'Enabled',
        'disabled' => 'Not enabled',
    ],

    'servers' => [
        'any' => 'Any',
        'has' => 'Has access to a server',
        'owner' => 'Owns a server',
        'subuser' => 'Is a subuser of a server',
        'none' => 'Has no servers',
    ],

    'button_styles' => [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
    ],

    'statuses' => [
        'active' => 'Active',
        'scheduled' => 'Scheduled',
        'expired' => 'Expired',
        'disabled' => 'Disabled',
    ],

    'columns' => [
        'name' => 'Name',
        'type' => 'Type',
        'status' => 'Status',
        'audience' => 'Audience',
        'placements' => 'Shown On',
        'enabled' => 'Enabled',
        'dismissals' => 'Dismissed',
        'clicks' => 'Clicks',
        'priority' => 'Priority',
        'ends_at' => 'Ends',
    ],

    'suffixes' => [
        'days' => 'days',
    ],

    'actions' => [
        'add_button' => 'Add Button',
        'duplicate' => 'Duplicate',
        'reset_dismissals' => 'Reset Dismissals',
        'reset_dismissals_description' => 'Everyone who closed this alert, or hid it by following a button, will see it again.',
    ],

    'notices' => [
        'dismissals_reset' => 'The alert is visible again for everyone.',
        'duplicated' => 'The alert was duplicated and left disabled.',
    ],

    'validation' => [
        'ends_after_start' => 'The end time must be after the start time.',
        'age_range' => 'The maximum account age must not be lower than the minimum.',
        'button_url' => 'Use a full URL starting with http:// or https://, a mailto: link, or a path starting with /.',
    ],

    'copy_suffix' => ':name (copy)',

];
