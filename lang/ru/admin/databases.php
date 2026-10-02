<?php

return [

    'label' => 'База данных',
    'plural-label' => 'Базы данных',

    'none' => 'Нет',

    'sections' => [
        'host_details' => [
            'title' => 'Данные хоста',
            'description' => 'Настройте параметры подключения к хосту базы данных.',
        ],

        'authentication' => [
            'title' => 'Аутентификация',
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
        'linked_node' => 'Необязательно. Свяжите этот хост с определённым узлом.',
    ],

    'fields' => [
        'linked_node' => 'Связанный узел',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Название',
        'host' => 'Хост',
        'port' => 'Порт',
        'username' => 'Имя пользователя',
        'linked_node' => 'Связанный узел',
        'databases' => 'Базы данных',
        'created' => 'Создано',
    ],

    'actions' => [
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
    ],

    'errors' => [
        'cannot_delete' => 'Невозможно удалить хост базы данных, с которым связаны базы данных.',
    ],

];
