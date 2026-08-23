<?php

return [
    'daemon_connection_failed' => 'Произошла ошибка при попытке связи с демоном, получен ответ HTTP/:code. Исключение записано в журнал.',
    'node' => [
        'servers_attached' => 'Для удаления ноды к ней не должно быть привязано ни одного сервера.',
        'daemon_off_config_updated' => 'Конфигурация демона обновлена, но при попытке автоматически обновить файл конфигурации на демоне возникла ошибка. Вам потребуется вручную обновить файл конфигурации (config.yml) для применения изменений.',
    ],
    'allocations' => [
        'server_using' => 'Этот порт в настоящее время назначен серверу. Порт можно удалить только если он не используется.',
        'too_many_ports' => 'Добавление более 1000 портов в одном диапазоне за раз не поддерживается.',
        'invalid_mapping' => 'Предоставленное сопоставление для порта :port недопустимо и не может быть обработано.',
        'cidr_out_of_range' => 'CIDR-нотация допускает маски только /25 и /32.',
        'port_out_of_range' => 'Порты в выделении должны быть больше 1024 и меньше или равны 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Невозможно удалить набор, к которому привязаны активные серверы.',
        'egg' => [
            'delete_has_servers' => 'Невозможно удалить шаблон, к которому привязаны активные серверы.',
            'invalid_copy_id' => 'Выбранный для копирования скрипт шаблон либо не существует, либо сам является копией другого шаблона.',
            'must_be_child' => 'Директива "Копировать настройки из" для этого шаблона должна указывать на дочерний элемент выбранного набора.',
            'has_children' => 'Этот шаблон является родительским для одного или нескольких других шаблонов. Пожалуйста, удалите их перед удалением данного шаблона.',
        ],
        'variables' => [
            'env_not_unique' => 'Переменная окружения :name должна быть уникальной для этого шаблона.',
            'reserved_name' => 'Переменная окружения :name зарезервирована и не может быть назначена.',
            'bad_validation_rule' => 'Правило валидации ":rule" не является допустимым для этого приложения.',
        ],
        'importer' => [
            'json_error' => 'Произошла ошибка при попытке разбора JSON-файла: :error.',
            'file_error' => 'Предоставленный JSON-файл недопустим.',
            'invalid_json_provided' => 'Предоставленный JSON-файл имеет нераспознаваемый формат.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Редактирование собственной учётной записи под пользователя не разрешено.',
        'user_is_owner' => 'Вы не можете добавить владельца сервера в качестве под пользователя.',
        'subuser_exists' => 'Пользователь с таким адресом электронной почты уже назначен под пользователем для этого сервера.',
    ],
    'subuser_preview' => [
        'start_blocked' => 'You cannot start another preview while preview mode is active.',
        'owner_only' => 'Only the server owner can preview a subuser.',
        'session_unavailable' => 'This preview session is no longer available.',
        'session_expired' => 'This preview session has expired.',
        'concurrent_start' => 'A preview session is already being started.',
        'account_unavailable' => 'Account information is unavailable during subuser preview.',
        'categories_unavailable' => 'Personal server categories are unavailable during subuser preview.',
        'permission_denied' => 'You do not have permission to perform this action in the preview.',
        'resource_unavailable' => 'This resource is unavailable during subuser preview.',
        'live_connection_unavailable' => 'This live connection is unavailable during subuser preview.',
        'file_not_found' => 'The requested file does not exist in this preview.',
        'file_too_large' => 'This file is too large to store in the preview.',
        'state_too_large' => 'This preview has reached its storage limit.',
        'unsafe_pull_url' => 'Only safe HTTPS URLs can be pulled into the preview.',
        'action_unavailable' => 'This action is not available during subuser preview.',
        'database_limit' => 'This server has reached its database limit.',
        'database_host_unavailable' => 'No database host is available for this server.',
        'task_limit' => 'This schedule has reached its task limit.',
        'allocation_limit' => 'This server has reached its allocation limit.',
        'allocation_unavailable' => 'No additional allocation is available for this server.',
        'primary_allocation' => 'The primary allocation cannot be removed.',
        'backup_limit' => 'This server has reached its backup limit.',
        'locked_backup' => 'A locked backup cannot be deleted.',
        'variable_unavailable' => 'The environment variable is unavailable or read-only.',
        'docker_image_unavailable' => 'The selected Docker image is unavailable for this server.',
    ],
    'databases' => [
        'delete_has_databases' => 'Невозможно удалить хост базы данных, к которому привязаны активные базы данных.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'Максимальный интервал для связанной задачи составляет 15 минут.',
    ],
    'locations' => [
        'has_nodes' => 'Невозможно удалить локацию, к которой привязаны активные ноды.',
    ],
    'users' => [
        'node_revocation_failed' => 'Не удалось отозвать ключи на <a href=":link"> Нода #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Не найдено ноды, удовлетворяющих требованиям для автоматического развёртывания.',
        'no_viable_allocations' => 'Не найдено распределений, удовлетворяющих требованиям для автоматического развёртывания.',
    ],
    'api' => [
        'resource_not_found' => 'Запрошенный ресурс не существует на этом сервере.',
    ],
    'social' => [
        'unlink_only_login' => 'Вы не можете отменить привязку к своему единственному методу входа в систему, предварительно не установив пароль.',
    ],
];
