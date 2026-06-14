<?php

return [
    'username-required' => 'ユーザー名またはメールアドレスを入力してください。',
    'password-required' => 'アカウントのパスワードを入力してください。',
    'email-required' => '続行するには有効なメールアドレスが必要です。',

    'login-title' => 'ログインして続行',

    'username-label' => 'ユーザー名またはメールアドレス',
    'password-label' => 'パスワード',

    'login-button' => 'ログイン',
    'return' => 'ログインに戻る',

    'social' => [
        'or' => 'または',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'このアカウントはいかなる :provider アカウントにもリンクされていない。まずメールアドレスとパスワードでログインし、アカウント設定ページから :provider アカウントをリンクすること。',
    ],

    'forgot-password' => [
        'title' => 'パスワードリセットをリクエスト',
        'label' => 'パスワードをお忘れですか？',
        'email-label' => 'メールアドレス',
        'email-content' => 'アカウントのメールアドレスを入力すると、パスワードリセットの手順が送信される。',
        'send-email' => 'メールを送信',
    ],

    'checkpoint' => [
        'title' => 'デバイスチェックポイント',
        'recovery-code' => 'リカバリーコード',
        'auth-code' => '認証コード',
        'is-missing' => '続行するには、このアカウントで 2 要素認証を設定した際に生成されたリカバリーコードのいずれかを入力すること。',
        'is-not-missing' => 'デバイスで生成された二要素認証トークンを入力してください。',
        'button' => '続行',
        'lost-device' => 'デバイスを紛失した',
        'not-lost-device' => 'デバイスを持っている',

    ],

    'reset-password' => [
        'new-required' => '新しいパスワードが必要です。',
        'min-required' => '新しいパスワードは 8 文字以上である必要があります。',
        'no-match' => '新しいパスワードが一致しません。',
        'email-label' => 'メールアドレス',
        'new-label' => '新しいパスワード',
        'min-length' => 'パスワードは 8 文字以上である必要があります。',
        'confirm-label' => '新しいパスワードを確認',
        'label' => 'パスワードをリセット',
    ],

    'register' => [
        'no-match' => 'パスワードが一致しません。',
        'namefirst-label' => '名',
        'namelast-label' => '姓',
        'email-label' => 'メールアドレス',
        'username-label' => 'ユーザー名',
        'password-label' => 'パスワード',
        'min-length' => 'パスワードは 8 文字以上である必要があります。',
        'confirm-label' => 'パスワードを確認',
        'label' => '登録',
        'create-account' => 'アカウントを作成',
    ],

    'failed' => 'この認証情報に一致するアカウントが見つかりません。',

    'two_factor' => [
        'label' => '2 要素認証トークン',
        'label_help' => 'このアカウントでは続行するために二段階認証が必要である。ログインを完了するには、デバイスで生成されたコードを入力すること。',
        'checkpoint_failed' => '二要素認証トークンが無効です。',
    ],

    'throttle' => 'ログイン試行回数が多すぎます。:seconds 秒後に再試行してください。',
    'password_requirements' => 'パスワードは 8 文字以上で、このサイト固有のものである必要があります。',
    '2fa_must_be_enabled' => '管理者はパネルを使用するために、アカウントで 2 要素認証を有効にすることを必須としている。',
];
