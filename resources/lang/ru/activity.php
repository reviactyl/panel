<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Пользователь системы',
        'system' => 'Система',
        'using-api-key' => 'Использование API-ключа',
        'using-sftp' => 'Использование SFTP',
    ],
    'auth' => [
        'fail' => 'Ошибка входа',
        'success' => 'Выполнен вход',
        'password-reset' => 'Сброс пароля',
        'reset-password' => 'Запрос сброса пароля',
        'checkpoint' => 'Запрошена двухфакторная аутентификация',
        'recovery-token' => 'Использован резервный код двухфакторный аутентификации',
        'token' => 'Пройдена проверка двухфакторный аутентификации',
        'ip-blocked' => 'Заблокирован запрос с неуказанного IP-адреса для :identifier',
        'sftp' => [
            'fail' => 'Ошибка входа через SFTP',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'Изменён адрес электронной почты с :old на :new',
            'password-changed' => 'Изменён пароль',
            'language-changed' => 'Язык изменён с :old на :new',
        ],
        'api-key' => [
            'create' => 'Создан новый API-ключ :identifier',
            'delete' => 'Удалён API-ключ :identifier',
        ],
        'ssh-key' => [
            'create' => 'Добавлен SSH-ключ :fingerprint в аккаунт',
            'delete' => 'Удалён SSH-ключ :fingerprint из аккаунта',
        ],
        'two-factor' => [
            'create' => 'Включена двухфакторная аутентификация',
            'delete' => 'Отключена двухфакторная аутентификация',
        ],
    ],
    'server' => [
        'reinstall' => 'Выполнена переустановка сервера',
        'console' => [
            'command' => 'Выполнена команда ":command" на сервере',
        ],
        'power' => [
            'start' => 'Сервер запущен',
            'stop' => 'Сервер остановлен',
            'restart' => 'Сервер перезапущен',
            'kill' => 'Процесс сервера принудительно завершён',
        ],
        'backup' => [
            'download' => 'Скачана резервная копия :name',
            'delete' => 'Удалена резервная копия :name',
            'restore' => 'Восстановлена резервная копия :name (удалено файлов: :truncate)',
            'restore-complete' => 'Завершено восстановление резервной копии :name',
            'restore-failed' => 'Не удалось завершить восстановление резервной копии :name',
            'start' => 'Начато создание резервной копии :name',
            'complete' => 'Резервная копия :name отмечена как завершённая',
            'fail' => 'Резервная копия :name отмечена как неудачная',
            'lock' => 'Резервная копия :name заблокирована',
            'unlock' => 'Резервная копия :name разблокирована',
        ],
        'database' => [
            'create' => 'Создана новая база данных :name',
            'rotate-password' => 'Изменён пароль для базы данных :name',
            'delete' => 'Удалена база данных :name',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => 'Сжато :count файлов в :directory',
            'read' => 'Просмотрено содержимое :file',
            'copy' => 'Создана копия :file',
            'create-directory' => 'Создана директория :directory:name',
            'decompress' => 'Распакованы :files в :directory',
            'delete_one' => 'Удалён :directory:files.0',
            'delete_other' => 'Удалено :count файлов в :directory',
            'download' => 'Скачан :file',
            'pull' => 'Скачан удалённый файл с :url в :directory',
            'rename_one' => 'Переименован :directory:files.0.from в :directory:files.0.to',
            'rename_other' => 'Переименовано :count файлов в :directory',
            'write' => 'Записано новое содержимое в :file',
            'upload' => 'Начата загрузка файла',
            'uploaded' => 'Загружен :directory:file',
        ],
        'sftp' => [
            'denied' => 'Доступ через SFTP заблокирован из-за ограничений прав',
            'create_one' => 'Создан :files.0',
            'create_other' => 'Создано :count новых файлов',
            'write_one' => 'Изменено содержимое :files.0',
            'write_other' => 'Изменено содержимое :count файлов',
            'delete_one' => 'Удалён :files.0',
            'delete_other' => 'Удалено :count файлов',
            'create-directory_one' => 'Создана директория :files.0',
            'create-directory_other' => 'Создано :count директорий',
            'rename_one' => 'Переименован :files.0.from в :files.0.to',
            'rename_other' => 'Переименовано или перемещено :count файлов',
        ],
        'allocation' => [
            'create' => 'Добавлен порт :allocation на сервер',
            'notes' => 'Обновлены примечания для :allocation с ":old" на ":new"',
            'primary' => 'Порт :allocation установлено как основной',
            'delete' => 'Удален порт :allocation',
        ],
        'schedule' => [
            'create' => 'Создано расписание :name',
            'update' => 'Обновлено расписание :name',
            'execute' => 'Вручную запущено расписание :name',
            'delete' => 'Удалено расписание :name',
        ],
        'task' => [
            'create' => 'Создана новая задача ":action" для расписания :name',
            'update' => 'Обновлена задача ":action" для расписания :name',
            'delete' => 'Удалена задача для расписания :name',
        ],
        'settings' => [
            'rename' => 'Сервер переименован с :old на :new',
            'description' => 'Описание сервера изменено с :old на :new',
        ],
        'startup' => [
            'edit' => 'Переменная :variable изменена с ":old" на ":new"',
            'image' => 'Образ Docker для сервера обновлён с :old на :new',
        ],
        'subuser' => [
            'create' => 'Добавлен подпользователь :email',
            'update' => 'Обновлены права подпользователя :email',
            'delete' => 'Удалён подпользователь :email',
        ],
    ],
];
