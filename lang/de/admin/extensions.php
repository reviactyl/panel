<?php

return [

    'label' => 'Verlängerung',
    'plural-label' => 'Erweiterungen',
    'marketplace_heading' => 'Verfügbare Erweiterungen',

    'columns' => [
        'icon' => 'Symbol',
        'id' => 'ID',
        'name' => 'Name',
        'version' => 'Version',
        'author' => 'Autor',
        'enabled' => 'Ermöglicht',
        'updated' => 'Aktualisiert',
        'last_updated' => 'Zuletzt aktualisiert',
        'downloads' => 'Downloads',
        'manifest_json' => 'JSON manifestieren',
        'file' => 'Wählen Sie eine .rext-Datei zum Hochladen aus',
    ],

    'modals' => [
        'manifest' => 'Erweiterungsmanifest',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'view' => 'Erweiterung herunterladen',
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
