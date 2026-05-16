<?php

return [

    'label' => 'Verlängerung',
    'plural-label' => 'Erweiterungen',

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'version' => 'Version',
        'author' => 'Autor',
        'enabled' => 'Ermöglicht',
        'updated' => 'Aktualisiert',
        'manifest_json' => 'JSON manifestieren',
    ],

    'modals' => [
        'manifest' => 'Erweiterungsmanifest',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
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
