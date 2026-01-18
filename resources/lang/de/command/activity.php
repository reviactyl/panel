<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Systembenutzer',
        'system' => 'System',
        'using-api-key' => 'Verwendet API-Key',
        'using-sftp' => 'Verwendet SFTP',
    ],
    'auth' => [
        'fail' => 'Anmeldung fehlgeschlagen',
        'success' => 'Angemeldet',
        'password-reset' => 'Passwort zurückgesetzt',
        'reset-password' => 'Passwort zurücksetzen angefordert',
        'checkpoint' => 'Zwei-Faktor-Authentifizierung angefordert',
        'recovery-token' => 'Zwei-Faktor-Wiederherstellungstoken verwendet',
        'token' => 'Zwei-Faktor-Herausforderung gelöst',
        'ip-blocked' => 'Anfrage von nicht gelisteter IP-Adresse für :identifier blockiert',
        'sftp' => [
            'fail' => 'SFTP-Anmeldung fehlgeschlagen',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'E-Mail von :old zu :new geändert',
            'password-changed' => 'Passwort geändert',
            'language-changed' => 'Sprache von :old zu :new geändert',
        ],
        'api-key' => [
            'create' => 'Neuen API-Schlüssel :identifier erstellt',
            'delete' => 'API-Schlüssel :identifier gelöscht',
        ],
        'ssh-key' => [
            'create' => 'SSH-Schlüssel :fingerprint zum Konto hinzugefügt',
            'delete' => 'SSH-Schlüssel :fingerprint vom Konto entfernt',
        ],
        'two-factor' => [
            'create' => 'Zwei-Faktor-Authentifizierung aktiviert',
            'delete' => 'Zwei-Faktor-Authentifizierung deaktiviert',
        ],
    ],
    'server' => [
        'reinstall' => 'Server neu installiert',
        'console' => [
            'command' => 'Befehl ":command" auf dem Server ausgeführt',
        ],
        'power' => [
            'start' => 'Server gestartet',
            'stop' => 'Server gestoppt',
            'restart' => 'Server neu gestartet',
            'kill' => 'Serverprozess gekillt',
        ],
        'backup' => [
            'download' => 'Backup :name heruntergeladen',
            'delete' => 'Backup :name gelöscht',
            'restore' => 'Backup :name wiederhergestellt (gelöschte Dateien: :truncate)',
            'restore-complete' => 'Wiederherstellung des Backups :name abgeschlossen',
            'restore-failed' => 'Wiederherstellung des Backups :name fehlgeschlagen',
            'start' => 'Neues Backup :name gestartet',
            'complete' => 'Backup :name als vollständig markiert',
            'fail' => 'Backup :name als fehlgeschlagen markiert',
            'lock' => 'Backup :name gesperrt',
            'unlock' => 'Backup :name entsperrt',
        ],
        'database' => [
            'create' => 'Neue Datenbank :name erstellt',
            'rotate-password' => 'Passwort für Datenbank :name rotiert',
            'delete' => 'Datenbank :name gelöscht',
        ],
        'file' => [
            'compress_one' => ':directory:file komprimiert',
            'compress_other' => ':count Dateien in :directory komprimiert',
            'read' => 'Inhalt von :file angesehen',
            'copy' => 'Kopie von :file erstellt',
            'create-directory' => 'Verzeichnis :directory:name erstellt',
            'decompress' => ':files in :directory dekomprimiert',
            'delete_one' => ':directory:files.0 gelöscht',
            'delete_other' => ':count Dateien in :directory gelöscht',
            'download' => ':file heruntergeladen',
            'pull' => 'Remote-Datei von :url nach :directory heruntergeladen',
            'rename_one' => ':directory:files.0.from in :directory:files.0.to umbenannt',
            'rename_other' => ':count Dateien in :directory umbenannt',
            'write' => 'Neuen Inhalt in :file geschrieben',
            'upload' => 'Datei-Upload begonnen',
            'uploaded' => ':directory:file hochgeladen',
        ],
        'sftp' => [
            'denied' => 'SFTP-Zugriff aufgrund von Berechtigungen blockiert',
            'create_one' => ':files.0 erstellt',
            'create_other' => ':count neue Dateien erstellt',
            'write_one' => 'Inhalt von :files.0 modifiziert',
            'write_other' => 'Inhalt von :count Dateien modifiziert',
            'delete_one' => ':files.0 gelöscht',
            'delete_other' => ':count Dateien gelöscht',
            'create-directory_one' => 'Verzeichnis :files.0 erstellt',
            'create-directory_other' => ':count Verzeichnisse erstellt',
            'rename_one' => ':files.0.from in :files.0.to umbenannt',
            'rename_other' => ':count Dateien umbenannt oder verschoben',
        ],
        'allocation' => [
            'create' => ':allocation zum Server hinzugefügt',
            'notes' => 'Notizen für :allocation von ":old" zu ":new" aktualisiert',
            'primary' => ':allocation als primäre Serverzuweisung festgelegt',
            'delete' => 'Zuweisung :allocation gelöscht',
        ],
        'schedule' => [
            'create' => 'Zeitplan :name erstellt',
            'update' => 'Zeitplan :name aktualisiert',
            'execute' => 'Zeitplan :name manuell ausgeführt',
            'delete' => 'Zeitplan :name gelöscht',
        ],
        'task' => [
            'create' => 'Neue Aufgabe ":action" für den Zeitplan :name erstellt',
            'update' => 'Aufgabe ":action" für den Zeitplan :name aktualisiert',
            'delete' => 'Aufgabe für den Zeitplan :name gelöscht',
        ],
        'settings' => [
            'rename' => 'Server von :old in :new umbenannt',
            'description' => 'Serverbeschreibung von :old zu :new geändert',
        ],
        'startup' => [
            'edit' => 'Variable :variable von ":old" zu ":new" geändert',
            'image' => 'Docker-Image für den Server von :old zu :new aktualisiert',
        ],
        'subuser' => [
            'create' => ':email als Subuser hinzugefügt',
            'update' => 'Subuser-Berechtigungen für :email aktualisiert',
            'delete' => ':email als Subuser entfernt',
        ],
    ],
];