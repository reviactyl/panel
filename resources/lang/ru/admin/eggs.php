<?php

return [

    'tabs' => [
        'configuration' => 'Настройка шаблона',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Конфигурация',
        ],
        'identity' => [
            'title' => 'Идентификация',
        ],
        'docker_images' => [
            'title' => 'Docker-образы',
            'description' => 'Docker-образы, доступные для серверов, использующих этот шаблон. Вводите по одному на строку.',
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
        'nest' => 'Набор',
        'uuid' => 'UUID',
        'name' => 'Имя',
        'author' => 'Автор',
        'image' => 'Образ',
        'description' => 'Описание',
        'image_name' => 'Имя образа',
        'image_uri' => 'URI образа',
        'add_docker_image' => 'Добавить Docker-образ',
        'force_outgoing_ip' => 'Принудительный исходящий IP',
        'features' => 'Функции',
        'startup' => 'Команда запуска',
        'config_stop' => 'Команда остановки',
        'config_from' => 'Копировать настройки из',
        'config_startup' => 'Конфигурация запуска (JSON)',
        'config_logs' => 'Конфигурация логов (JSON)',
        'config_files' => 'Конфигурационные файлы (JSON)',
        'file_denylist' => 'Список запрещённых файлов',
        'env_variable' => 'Переменная окружения',
        'user_viewable' => 'Пользователи могут просматривать',
        'user_editable' => 'Пользователи могут редактировать',
        'rules' => 'Правила ввода',
        'default_value' => 'Значение по умолчанию',
        'script_install' => 'Скрипт установки',
        'script_container' => 'Контейнер скрипта',
        'script_entry' => 'Команда точки входа скрипта',
        'copy_script_from' => 'Копировать скрипт из',
        'script_is_privileged' => 'Контейнер с правами root (privileged)',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Принудительно направляет весь исходящий сетевой трафик через NAT к основному IP-адресу распределения сервера.',
        'features' => 'Дополнительные функции, относящиеся к шаблону. Полезно для настройки модификаций панели.',
        'file_denylist' => 'Файлы, которые не должны редактироваться пользователем.',
        'script_is_privileged' => 'Запускать скрипт установки в контейнере с правами root (privileged).',
    ],

    'actions' => [
        'export' => 'Экспорт',
        'create' => 'Создать шаблон',
        'edit' => 'Редактировать',
    ],

    'notices' => [
        'cannot_delete' => 'Невозможно удалить шаблон',
        'cannot_delete_body' => 'С этим шаблоном связано :count сервер(ов). Сначала удалите или переназначьте их.',
        'cannot_delete_multiple' => 'Невозможно удалить шаблоны с привязанными серверами',
        'cannot_delete_multiple_body' => ':count шаблон(ов) имеют связанные серверы и были пропущены.',
    ],

];
