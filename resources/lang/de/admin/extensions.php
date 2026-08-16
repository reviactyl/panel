<?php

return [

    'label' => 'Verlängerung',
    'plural-label' => 'Erweiterungen',
    'marketplace_heading' => 'Available Extensions',

    'columns' => [
        'icon' => 'Icon',
        'id' => 'ID',
        'name' => 'Name',
        'version' => 'Version',
        'author' => 'Autor',
        'enabled' => 'Ermöglicht',
        'updated' => 'Aktualisiert',
        'last_updated' => 'Last Updated',
        'downloads' => 'Downloads',
        'manifest_json' => 'JSON manifestieren',
        'file' => 'Choose a .rext file to upload',
    ],

    'modals' => [
        'manifest' => 'Erweiterungsmanifest',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'view' => 'Get Extension',
        'upload' => 'Hochladen',
        'manifest' => 'Manifest anzeigen',
        'disable' => 'Deaktivieren',
        'enable' => 'Aktivieren',
        'delete' => 'Löschen',
        'close' => 'Schließen',
    ],

    'alerts' => [
        'enabled' => 'Erweiterung aktiviert.',
        'enable_failed' => 'Die Erweiterung konnte nicht aktiviert werden.',
        'disabled' => 'Erweiterung deaktiviert.',
        'disable_failed' => 'Die Erweiterung konnte nicht deaktiviert werden.',
        'uninstalled' => 'Erweiterung deinstalliert.',
        'uninstall_failed' => 'Die Deinstallation der Erweiterung ist fehlgeschlagen.',
        'could_not_locate_file' => 'Die hochgeladene Paketdatei konnte nicht gefunden werden.',
        'invalid_file_type' => 'Es sind nur .rext-Dateien zulässig.',
        'upload_hint' => 'Es sind nur .rext-Erweiterungspakete zulässig.',
        'install_failed' => 'Die Installation der Erweiterung ist fehlgeschlagen.',
        'install_success' => ':name (:version) erfolgreich installiert.',
    ],

];
