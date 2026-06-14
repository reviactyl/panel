<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'システムユーザー',
        'system' => 'システム',
        'using-api-key' => 'API キーを使用',
        'using-sftp' => 'SFTP を使用',
    ],
    'auth' => [
        'fail' => 'ログイン失敗',
        'success' => 'ログイン成功',
        'password-reset' => 'パスワードをリセット',
        'reset-password' => 'パスワードリセットをリクエスト',
        'checkpoint' => '二要素認証をリクエスト',
        'recovery-token' => '二要素認証リカバリートークンを使用',
        'token' => '二要素認証チャレンジを完了',
        'ip-blocked' => ':identifier の未登録 IP アドレスからのリクエストをブロック',
        'sftp' => [
            'fail' => 'SFTP ログイン失敗',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'メールアドレスを :old から :new に変更',
            'password-changed' => 'パスワードを変更',
            'language-changed' => '言語を :old から :new に変更',
        ],
        'api-key' => [
            'create' => 'API キー :identifier を作成',
            'delete' => 'API キー :identifier を削除',
        ],
        'ssh-key' => [
            'create' => 'SSH キー :fingerprint をアカウントに追加',
            'delete' => 'SSH キー :fingerprint をアカウントから削除',
        ],
        'two-factor' => [
            'create' => '二要素認証を有効化',
            'delete' => '二要素認証を無効化',
        ],
    ],
    'server' => [
        'reinstall' => 'サーバーを再インストール',
        'console' => [
            'command' => 'サーバーで ":command" を実行',
        ],
        'power' => [
            'start' => 'サーバーを起動',
            'stop' => 'サーバーを停止',
            'restart' => 'サーバーを再起動',
            'kill' => 'サーバープロセスを強制終了',
        ],
        'backup' => [
            'download' => 'バックアップ :name をダウンロード',
            'delete' => 'バックアップ :name を削除',
            'restore' => 'バックアップ :name を復元（ファイル削除: :truncate）',
            'restore-complete' => 'バックアップ :name の復元を完了',
            'restore-failed' => 'バックアップ :name の復元に失敗',
            'start' => '新しいバックアップ :name を開始',
            'complete' => 'バックアップ :name を完了済みとしてマーク',
            'fail' => 'バックアップ :name を失敗としてマーク',
            'lock' => 'バックアップ :name をロック',
            'unlock' => 'バックアップ :name のロックを解除',
        ],
        'database' => [
            'create' => 'データベース :name を作成',
            'rotate-password' => 'データベース :name のパスワードをローテーション',
            'delete' => 'データベース :name を削除',
        ],
        'file' => [
            'compress_one' => ':directory:file を圧縮',
            'compress_other' => ':directory 内の :count 個のファイルを圧縮',
            'read' => ':file の内容を表示',
            'copy' => ':file のコピーを作成',
            'create-directory' => 'ディレクトリ :directory:name を作成',
            'decompress' => ':directory 内の :files を解凍',
            'delete_one' => ':directory:files.0 を削除',
            'delete_other' => ':directory 内の :count 個のファイルを削除',
            'download' => ':file をダウンロード',
            'pull' => ':url からリモートファイルを :directory にダウンロード',
            'rename_one' => ':directory:files.0.from を :directory:files.0.to にリネーム',
            'rename_other' => ':directory 内の :count 個のファイルをリネーム',
            'write' => ':file に新しい内容を書き込み',
            'upload' => 'ファイルのアップロードを開始',
            'uploaded' => ':directory:file をアップロード',
        ],
        'sftp' => [
            'denied' => '権限不足のため SFTP アクセスをブロック',
            'create_one' => ':files.0 を作成',
            'create_other' => ':count 個の新しいファイルを作成',
            'write_one' => ':files.0 の内容を変更',
            'write_other' => ':count 個のファイルの内容を変更',
            'delete_one' => ':files.0 を削除',
            'delete_other' => ':count 個のファイルを削除',
            'create-directory_one' => ':files.0 ディレクトリを作成',
            'create-directory_other' => ':count 個のディレクトリを作成',
            'rename_one' => ':files.0.from を :files.0.to にリネーム',
            'rename_other' => ':count 個のファイルをリネームまたは移動',
        ],
        'allocation' => [
            'create' => ':allocation をサーバーに追加',
            'notes' => ':allocation のメモを ":old" から ":new" に更新',
            'primary' => ':allocation をプライマリ割り当てに設定',
            'delete' => 'アロケーション :allocation を削除',
        ],
        'schedule' => [
            'create' => 'スケジュール :name を作成',
            'update' => 'スケジュール :name を更新',
            'execute' => 'スケジュール :name を手動実行',
            'delete' => 'スケジュール :name を削除',
        ],
        'task' => [
            'create' => 'スケジュール :name に新しい ":action" タスクを作成',
            'update' => 'スケジュール :name の ":action" タスクを更新',
            'delete' => 'スケジュール :name のタスクを削除',
        ],
        'settings' => [
            'rename' => 'サーバー名を :old から :new に変更',
            'description' => 'サーバーの説明を :old から :new に変更',
        ],
        'startup' => [
            'edit' => '変数 :variable を ":old" から ":new" に変更',
            'image' => 'サーバーの Docker イメージを :old から :new に更新',
        ],
        'subuser' => [
            'create' => ':email をサブユーザーとして追加',
            'update' => ':email のサブユーザー権限を更新',
            'delete' => ':email のサブユーザーを削除',
        ],
    ],
];
