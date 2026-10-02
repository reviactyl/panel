<?php

return [

    'tabs' => [
        'configuration' => 'Конфигурация Egg',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Конфигурация',
        ],
        'identity' => [
            'title' => 'Идентификация',
        ],
        'docker_images' => [
            'title' => 'Образы Docker',
            'description' => 'Образы Docker, доступные серверам, использующим этот Egg. Укажите по одному образу на строку.',
        ],
        'process_management' => [
            'title' => 'Управление процессами',
        ],
        'variables' => [
            'title' => 'Переменные',
        ],
        'install_script' => [
            'title' => 'Скрипт установки',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'name' => 'Название',
        'author' => 'Автор',
        'image' => 'Образ',
        'description' => 'Описание',
        'image_name' => 'Название образа',
        'image_uri' => 'URI образа',
        'add_docker_image' => 'Добавить образ Docker',
        'force_outgoing_ip' => 'Принудительный исходящий IP',
        'features' => 'Функции',
        'startup' => 'Команда запуска',
        'config_stop' => 'Команда остановки',
        'config_from' => 'Копировать настройки из',
        'config_startup' => 'Конфигурация запуска (JSON)',
        'config_logs' => 'Конфигурация журналов (JSON)',
        'config_files' => 'Файлы конфигурации (JSON)',
        'file_denylist' => 'Список запрещённых файлов',
        'env_variable' => 'Переменная окружения',
        'user_viewable' => 'Пользователи могут просматривать',
        'user_editable' => 'Пользователи могут редактировать',
        'rules' => 'Правила ввода',
        'default_value' => 'Значение по умолчанию',
        'script_install' => 'Скрипт установки',
        'script_container' => 'Контейнер скрипта',
        'script_entry' => 'Команда запуска скрипта',
        'copy_script_from' => 'Копировать скрипт из',
        'script_is_privileged' => 'Привилегированный режим',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Принудительно устанавливает исходный IP-адрес для всего исходящего сетевого трафика через NAT на IP-адрес основного распределения сервера.',
        'features' => 'Дополнительные функции, относящиеся к Egg. Полезно для настройки дополнительных модификаций панели.',
        'file_denylist' => 'Файлы, которые пользователь не должен редактировать.',
        'script_is_privileged' => 'Запускать скрипт установки в привилегированном контейнере (root).',
    ],

    'actions' => [
        'export' => 'Экспортировать',
        'create' => 'Создать Egg',
        'edit' => 'Редактировать',
    ],

    'notices' => [
        'cannot_delete' => 'Невозможно удалить Egg',
        'cannot_delete_body' => 'С этим Egg связано серверов: :count. Сначала удалите их или назначьте другому Egg.',
        'cannot_delete_multiple' => 'Невозможно удалить Egg, связанные с серверами',
        'cannot_delete_multiple_body' => 'Egg, связанных с серверами: :count. Они были пропущены.',
    ],

];
