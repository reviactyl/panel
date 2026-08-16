<?php

return [

    'label' => 'Расширение',
    'plural-label' => 'Расширения',
    'marketplace_heading' => 'Available Extensions',

    'columns' => [
        'icon' => 'Icon',
        'id' => 'ID',
        'name' => 'Название',
        'version' => 'Версия',
        'author' => 'Автор',
        'enabled' => 'Включено',
        'updated' => 'Обновлено',
        'last_updated' => 'Last Updated',
        'downloads' => 'Downloads',
        'manifest_json' => 'Манифест JSON',
        'file' => 'Choose a .rext file to upload',
    ],

    'modals' => [
        'manifest' => 'Манифест расширения',
    ],

    'actions' => [
        'edit' => 'Редактировать',
        'view' => 'Get Extension',
        'upload' => 'Загрузить',
        'manifest' => 'Просмотреть манифест',
        'disable' => 'Отключить',
        'enable' => 'Включить',
        'delete' => 'Удалить',
        'close' => 'Закрыть',
    ],

    'alerts' => [
        'enabled' => 'Расширение включено.',
        'enable_failed' => 'Не удалось включить расширение.',
        'disabled' => 'Расширение отключено.',
        'disable_failed' => 'Не удалось отключить расширение.',
        'uninstalled' => 'Расширение удалено.',
        'uninstall_failed' => 'Не удалось удалить расширение.',
        'could_not_locate_file' => 'Не удалось найти загруженный файл пакета.',
        'invalid_file_type' => 'Разрешены только файлы .rext.',
        'upload_hint' => 'Разрешены только пакеты расширений .rext.',
        'install_failed' => 'Не удалось установить расширение.',
        'install_success' => 'Расширение :name (:version) успешно установлено.',
    ],

];
