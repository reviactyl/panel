<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => '系统用户',
        'system' => '系统',
        'using-api-key' => '使用 API 密钥',
        'using-sftp' => '使用 SFTP',
    ],
    'auth' => [
        'fail' => '登录失败',
        'success' => '登录成功',
        'password-reset' => '密码已重置',
        'reset-password' => '请求重置密码',
        'checkpoint' => '请求双重认证',
        'recovery-token' => '使用了双重认证恢复令牌',
        'token' => '已解决双重认证挑战',
        'ip-blocked' => '已阻止来自未列出 IP 地址的 :identifier 请求',
        'sftp' => [
            'fail' => 'SFTP 登录失败',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => '邮箱已从 :old 更改为 :new',
            'password-changed' => '密码已更改',
            'language-changed' => '语言已从 :old 更改为 :new',
        ],
        'api-key' => [
            'create' => '创建了新的 API 密钥 :identifier',
            'delete' => '删除了 API 密钥 :identifier',
        ],
        'ssh-key' => [
            'create' => '已将 SSH 密钥 :fingerprint 添加到账户',
            'delete' => '已从账户中移除 SSH 密钥 :fingerprint',
        ],
        'two-factor' => [
            'create' => '启用了双重认证',
            'delete' => '禁用了双重认证',
        ],
    ],
    'server' => [
        'reinstall' => '重新安装了服务器',
        'console' => [
            'command' => '在服务器上执行了 ":command" 命令',
        ],
        'power' => [
            'start' => '启动了服务器',
            'stop' => '停止了服务器',
            'restart' => '重启了服务器',
            'kill' => '强制终止了服务器进程',
        ],
        'backup' => [
            'download' => '下载了 :name 备份',
            'delete' => '删除了 :name 备份',
            'restore' => '恢复了 :name 备份（已删除文件：:truncate）',
            'restore-complete' => '已完成 :name 备份的恢复',
            'restore-failed' => '未能完成 :name 备份的恢复',
            'start' => '开始了一个新的备份 :name',
            'complete' => '已将 :name 备份标记为完成',
            'fail' => '已将 :name 备份标记为失败',
            'lock' => '锁定了 :name 备份',
            'unlock' => '解锁了 :name 备份',
        ],
        'database' => [
            'create' => '创建了新的数据库 :name',
            'rotate-password' => '数据库 :name 的密码已轮换',
            'delete' => '删除了数据库 :name',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => '在 :directory 目录中压缩了 :count 个文件',
            'read' => '查看了 :file 的内容',
            'copy' => '创建了 :file 的一个副本',
            'create-directory' => '创建了目录 :directory:name',
            'decompress' => '在 :directory 目录中解压缩了 :files',
            'delete_one' => '删除了 :directory:files.0',
            'delete_other' => '在 :directory 目录中删除了 :count 个文件',
            'download' => '下载了 :file',
            'pull' => '从 :url 下载了一个远程文件到 :directory',
            'rename_one' => '将 :directory:files.0.from 重命名为 :directory:files.0.to',
            'rename_other' => '在 :directory 目录中重命名了 :count 个文件',
            'write' => '向 :file 写入了新内容',
            'upload' => '开始了一个文件上传',
            'uploaded' => '上传了 :directory:file',
        ],
        'sftp' => [
            'denied' => '由于权限问题，SFTP 访问被阻止',
            'create_one' => '创建了 :files.0',
            'create_other' => '创建了 :count 个新文件',
            'write_one' => '修改了 :files.0 的内容',
            'write_other' => '修改了 :count 个文件的内容',
            'delete_one' => '删除了 :files.0',
            'delete_other' => '删除了 :count 个文件',
            'create-directory_one' => '创建了 :files.0 目录',
            'create-directory_other' => '创建了 :count 个目录',
            'rename_one' => '将 :files.0.from 重命名为 :files.0.to',
            'rename_other' => '重命名或移动了 :count 个文件',
        ],
        'allocation' => [
            'create' => '已向服务器添加 :allocation',
            'notes' => '已将 :allocation 的备注从 “:old” 更新为 “:new”',
            'primary' => '将 :allocation 设置为服务器的主分配项',
            'delete' => '已删除 :allocation 分配项',
        ],
        'schedule' => [
            'create' => '创建了计划任务 :name',
            'update' => '更新了计划任务 :name',
            'execute' => '手动执行了计划任务 :name',
            'delete' => '删除了计划任务 :name',
        ],
        'task' => [
            'create' => '为计划任务 :name 创建了一个新的 ":action" 任务',
            'update' => '更新了计划任务 :name 的 ":action" 任务',
            'delete' => '删除了计划任务 :name 的一个任务',
        ],
        'settings' => [
            'rename' => '将服务器名称从 :old 重命名为 :new',
            'description' => '将服务器描述从 :old 更改为 :new',
        ],
        'startup' => [
            'edit' => '将 :variable 变量从 ":old" 更改为 ":new"',
            'image' => '将服务器的 Docker 镜像从 :old 更新为 :new',
        ],
        'subuser' => [
            'create' => '添加了 :email 作为子用户',
            'update' => '更新了子用户 :email 的权限',
            'delete' => '移除了 :email 作为子用户',
        ],
    ],
];
