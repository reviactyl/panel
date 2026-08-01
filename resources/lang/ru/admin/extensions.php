<?php

return [

    'label' => 'Расширения',
    'plural-label' => 'Расширения',

    'columns' => [
        'id' => 'ID',
        'name' => 'Имя',
        'version' => 'Версия',
        'author' => 'Автор',
        'enabled' => 'Включен',
        'updated' => 'Обновлен',
        'manifest_json' => 'Манифест JSON',
    ],

    'modals' => [
        'manifest' => 'Манифест расширения',
    ],

    'actions' => [
        'edit' => 'Редактировать',
        'upload' => 'Загрузить',
        'manifest' => 'Просмотреть манифест',
        'disable' => 'Выключен',
        'enable' => 'Включен',
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
        'install_success' => 'Установлен :name (:version) успешно.',
    ],

];
