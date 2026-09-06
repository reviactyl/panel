<?php

return [
    'site_title' => 'Reviactyl',
    'site_description' => 'Our official control panel made better with Reviactyl.',
    'site_image' => '/reviactyl/logo.png',
    'site_favicon' => '/reviactyl/icon.png',
    'site_color' => '#3b82f6',

    'colorPrimary' => '#3b82f6',
    'colorSuccess' => '#3D8F1F',
    'colorDanger' => '#8F1F20',
    'colorSecondary' => '#2B2B40',

    'color50' => '#fafafa',
    'color100' => '#f4f4f5',
    'color200' => '#e4e4e7',
    'color300' => '#d4d4d8',
    'color400' => '#9f9fa9',
    'color500' => '#71717b',
    'color600' => '#52525c',
    'color700' => '#3f3f46',
    'color800' => '#27272a',
    'color900' => '#18181b',
    'color950' => '#09090b',

    'sidebarLogout' => false,
    'sidebarButtons' => '[]',
    'background' => 'none',
    'radius' => '15px',
    'allocationBlur' => true,
    'fontFamily' => 'Poppins',

    'customCopyright' => true,
    'copyright' => 'Designed with [Designify](https://reviactyl.app/designify)',

    'isUnderMaintenance' => false,
    'maintenance' => 'We are currently under maintenance. Kindly check back later!',

    'alertType' => 'info',
    'alertMessage' => '**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area.',
    'alerts' => '[{"type":"info","message":"**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area."}]',

    'errors' => [
        '403' => [
            'title' => 'Access Forbidden',
            'message' => 'You do not have permission to access this resource. Please contact the administrator if you believe this is an error.',
            'button' => 'Back to Dashboard',
            'image' => '',
            'color' => '#f59e0b',
        ],
        '404' => [
            'title' => 'Page Not Found',
            'message' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
            'button' => 'Back to Dashboard',
            'image' => '',
            'color' => '#3b82f6',
        ],
        '500' => [
            'title' => 'Internal Server Error',
            'message' => 'We encountered an error while processing your request. Please try again later or contact support if the problem persists.',
            'button' => 'Try Again',
            'image' => '',
            'color' => '#ef4444',
        ],
    ],

    'statusCardLink' => '',
    'supportCardLink' => '',
    'billingCardLink' => '',

    'alwaysShowKillButton' => false,

    'cardType' => 'grid',

    'layoutType' => 'modern',

    'avatarType' => 'gravatar',
];
