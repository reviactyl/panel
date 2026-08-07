<?php

return [
    'navigation' => [
        'label' => 'Мониторинг',
        'group' => 'Администрирование',
    ],

    'page' => [
        'title' => 'Мониторинг',
        'heading' => 'Мониторинг в реальном времени',
    ],

    'actions' => [
        'refresh' => 'Обновить данные',
    ],

    'selector' => [
        'label' => 'Выберите узел',
        'placeholder' => 'Выберите узел...',
    ],

    'stats' => [
        'cpu_usage' => 'Использование ЦП',
        'cpu_cores' => 'Доступно ядер: :count',
        'memory_usage' => 'Использование памяти',
        'disk_usage' => 'Использование диска',
        'network_traffic' => 'Сетевой трафик',
        'uptime' => 'Время работы',
        'last_updated' => 'Последнее обновление',
        'no_node' => 'Узел не выбран',
        'no_node_desc' => 'Выберите узел для просмотра данных мониторинга',
        'no_node_hint' => 'Используйте выпадающий список выше',
        'error' => 'Ошибка',
        'error_desc' => 'Не удалось загрузить данные мониторинга',
        'error_fetch' => 'Не удалось получить данные от Agent',
        'error_node_gone' => 'Узел больше не существует',
    ],

    'details' => [
        'heading' => 'Сведения о системе',
        'button' => 'Подробнее',
        'close' => 'Закрыть',
        'no_data' => 'Данные недоступны. Убедитесь, что узел находится в сети.',

        'cpu_section' => 'ЦП',
        'cpu_total' => 'Общая загрузка',
        'cpu_cores' => 'Ядра',
        'per_core' => 'Загрузка по ядрам',

        'memory_section' => 'Память',
        'total_memory' => 'Всего',
        'used_memory' => 'Использовано',
        'free_memory' => 'Свободно',
        'available_memory' => 'Доступно',

        'swap_section' => 'Swap',
        'swap_none' => 'На этом узле Swap не настроен.',
        'swap_total' => 'Всего',
        'swap_used' => 'Использовано',
        'swap_free' => 'Свободно',
        'swap_usage' => 'Использование',

        'partitions_section' => 'Disk Partitions',
        'partitions_none' => 'No partition data available.',
        'partitions_device' => 'Device',
        'partitions_mountpoint' => 'Mount Point',
        'partitions_filesystem' => 'Filesystem',
        'partitions_size' => 'Size',
        'partitions_usage' => 'Usage',

        'network_section' => 'Сеть',
        'bytes_sent' => 'Отправлено байт',
        'bytes_recv' => 'Получено байт',
        'packets_sent' => 'Отправлено пакетов',
        'packets_received' => 'Получено пакетов',

        'runtime_section' => 'Среда выполнения',
        'go_version' => 'Версия Go',
        'arch' => 'Архитектура',
        'goroutines' => 'Горутины',
        'uptime' => 'Время работы',
    ],
    'servers' => [
        'heading' => 'Использование ресурсов серверами',
        'no_node' => 'Выберите узел для просмотра использования ресурсов серверами.',
        'no_servers' => 'На этом узле серверы не найдены.',
        'error_fetch' => 'Не удалось получить данные серверов от Agent.',
        'col' => [
            'name' => 'Сервер',
            'state' => 'Состояние',
            'cpu' => 'ЦП',
            'memory' => 'Память',
            'disk' => 'Диск',
            'network' => 'Сеть',
            'uptime' => 'Время работы',
        ],
        'states' => [
            'running' => 'Работает',
            'starting' => 'Запускается',
            'stopping' => 'Останавливается',
            'offline' => 'Не в сети',
            'crashed' => 'Аварийно завершён',
            'unknown' => 'Неизвестно',
        ],
    ],
];
