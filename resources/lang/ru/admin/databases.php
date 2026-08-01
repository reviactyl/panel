<?php

return [

    'label' => 'База данных',
    'plural-label' => 'База данных',

    'none' => 'Ничего',

    'sections' => [
        'host_details' => [
            'title' => 'Сведения о хосте',
            'description' => 'Настройте параметры подключения к хосту базы данных.',
        ],

        'authentication' => [
            'title' => 'Идентификация',
        ],

        'linked_node' => [
            'title' => 'Связанный узел',
        ],
    ],

    'placeholders' => [
        'name' => 'Production MySQL',
    ],

    'helpers' => [
        'host' => 'Имя хоста или IP-адрес сервера базы данных.',
        'linked_node' => 'Необязательный. Свяжите этот хост с определенным узлом.',
    ],

    'fields' => [
        'linked_node' => 'Подключенный узел',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Имя',
        'host' => 'Хост',
        'port' => 'Порт',
        'username' => 'Логин',
        'linked_node' => 'Подключенный узел',
        'databases' => 'База данных',
        'created' => 'Создан',
    ],

    'actions' => [
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
    ],

    'errors' => [
        'cannot_delete' => 'Не удается удалить узел базы данных со связанными с ним базами данных.',
    ],

];
