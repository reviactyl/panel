<?php

return [

    'label' => 'Erweiterung',
    'plural-label' => 'Erweiterungen',

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'version' => 'Version',
        'author' => 'Autor',
        'enabled' => 'Aktiviert',
        'updated' => 'Aktualisiert',
        'manifest_json' => 'Manifest JSON',
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
        'enable_failed' => 'Aktivierung der Erweiterung fehlgeschlagen.',
        'disabled' => 'Erweiterung deaktiviert.',
        'disable_failed' => 'Deaktivierung der Erweiterung fehlgeschlagen.',
        'uninstalled' => 'Erweiterung deinstalliert.',
        'uninstall_failed' => 'Deinstallation der Erweiterung fehlgeschlagen.',
        'could_not_locate_file' => 'Die hochgeladene Paketdatei konnte nicht gefunden werden.',
        'invalid_file_type' => 'Nur .rext-Dateien sind erlaubt.',
        'upload_hint' => 'Nur .rext-Erweiterungspakete sind erlaubt.',
        'install_failed' => 'Installation der Erweiterung fehlgeschlagen.',
        'install_success' => ':name (:version) erfolgreich installiert.',
    ],

];
