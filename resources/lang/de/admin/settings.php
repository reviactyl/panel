<?php

return [
    'title' => 'Einstellungen',
    'sftp' => [
        'title' => 'SFTP-Details',
        'address' => 'Serveradresse',
        'username' => 'Benutzername',
        'password' => 'Ihr SFTP-Passwort ist dasselbe wie das Passwort, das Sie für den Zugriff auf dieses Panel verwenden.',
        'button' => 'SFTP starten',
    ],
    'info' => [
        'title' => 'Debug-Informationen',
        'node' => 'Node',
        'server' => 'Server-ID',
    ],
    'rename' => [
        'title' => 'Serverdetails ändern',
        'name' => 'Servername',
        'description' => 'Serverbeschreibung',
        'button' => 'Speichern',
    ],
    'reinstall' => [
        'title' => 'Server neu installieren',
        'confirm-title' => 'Server-Neuinstallation bestätigen',
        'confirm' => 'Ja, Server neu installieren',
        'info' => 'Ihr Server wird gestoppt und einige Dateien können während dieses Vorgangs gelöscht oder geändert werden. Sind Sie sicher, dass Sie fortfahren möchten?',
        'info-1' => 'Die Neuinstallation Ihres Servers stoppt ihn und führt dann das Installationsskript erneut aus, das ihn ursprünglich eingerichtet hat.',
        'info-2' => 'Einige Dateien können während dieses Vorgangs gelöscht oder geändert werden. Bitte sichern Sie Ihre Daten, bevor Sie fortfahren.',
        'button' => 'Server neu installieren',
    ],
];