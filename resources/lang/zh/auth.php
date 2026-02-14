<?php

return [
    'username-required' => '必须提供用户名或电子邮件地址。',
    'password-required' => '请输入您的账户密码。',
    'email-required' => '必须提供一个有效的电子邮件地址才能继续。',

    'login-title' => '登录以继续',

    'username-label' => '用户名或电子邮件',
    'password-label' => '密码',

    'login-button' => '登录',
    'return' => '返回登录',

    'social' => [
        'or' => '或',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'Github',
        'not_linked' => '此账户尚未关联任何 :provider 账户。请先使用邮箱和密码登录，然后在账户设置页面中关联你的 :provider 账户。',
    ],

    'forgot-password' => [
        'title' => '请求重置密码',
        'label' => '忘记密码？',
        'email-label' => '电子邮件',
        'email-content' => '输入您的账户电子邮件地址，以接收重置密码的代码。',
        'send-email' => '发送邮件',
    ],

    'checkpoint' => [
        'title' => '设备安全检查',
        'recovery-code' => '恢复代码',
        'auth-code' => '验证码',
        'is-missing' => '请输入您在为此账户设置2步身份验证时生成的恢复代码之一以继续。',
        'is-not-missing' => '请输入您的设备生成的双重认证令牌。',
        'button' => '继续',
        'lost-device' => '我遗失了我的设备',
        'not-lost-device' => '我有我的设备',

    ],

    'reset-password' => [
        'new-required' => '需要一个新密码。',
        'min-required' => '您的新密码长度应至少为 8 个字符。',
        'no-match' => '您的新密码不匹配。',
        'email-label' => '电子邮件',
        'new-label' => '新密码',
        'min-length' => '密码长度必须至少为 8 个字符。',
        'confirm-label' => '确认新密码',
        'label' => '重置密码',
    ],

    'register' => [
        'no-match' => '您的密码不匹配。',
        'namefirst-label' => '名字',
        'namelast-label' => '姓氏',
        'email-label' => '电子邮箱',
        'username-label' => '用户名',
        'password-label' => '密码',
        'min-length' => '密码长度至少为8个字符。',
        'confirm-label' => '确认密码',
        'label' => '注册',
    ],
    
    'failed' => '未找到与这些凭证匹配的账户。',

    'two_factor' => [
        'label' => '2步身份验证令牌',
        'label_help' => '此账户需要第二层身份验证才能继续。请输入您的设备生成的代码以完成此次登录。',
        'checkpoint_failed' => '双重认证令牌无效。',
    ],

    'throttle' => '登录尝试次数过多。请在 :seconds 秒后重试。',
    'password_requirements' => '密码长度必须至少为 8 个字符，且对此站点应是唯一的。',
    '2fa_must_be_enabled' => '管理员要求您启用2步身份验证后才能使用此面板。',
];
