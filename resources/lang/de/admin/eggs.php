<?php

return [

    'tabs' => [
        'configuration' => 'Egg-Konfiguration',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Konfiguration',
        ],
        'identity' => [
            'title' => 'Identität',
        ],
        'docker_images' => [
            'title' => 'Docker-Images',
            'description' => 'Die Docker-Images, die für Server mit diesem Egg verfügbar sind. Eines pro Zeile eingeben.',
        ],
        'process_management' => [
            'title' => 'Prozessverwaltung',
        ],
        'variables' => [
            'title' => 'Variablen',
        ],
        'install_script' => [
            'title' => 'Installationsskript',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'name' => 'Name',
        'author' => 'Autor',
        'image' => 'Image',
        'description' => 'Beschreibung',
        'image_name' => 'Image-Name',
        'image_uri' => 'Image-URI',
        'add_docker_image' => 'Docker-Image hinzufügen',
        'force_outgoing_ip' => 'Ausgehende IP erzwingen',
        'features' => 'Funktionen',
        'startup' => 'Startbefehl',
        'config_stop' => 'Stop-Befehl',
        'config_from' => 'Einstellungen kopieren von',
        'config_startup' => 'Startkonfiguration (JSON)',
        'config_logs' => 'Log-Konfiguration (JSON)',
        'config_files' => 'Konfigurationsdateien (JSON)',
        'file_denylist' => 'Sperrliste für Dateien',
        'env_variable' => 'Umgebungsvariable',
        'user_viewable' => 'Benutzer können sehen',
        'user_editable' => 'Benutzer können bearbeiten',
        'rules' => 'Validierungsregeln',
        'default_value' => 'Standardwert',
        'script_install' => 'Installationsskript',
        'script_container' => 'Skript-Container',
        'script_entry' => 'Skript-Einstiegspunkt',
        'copy_script_from' => 'Skript kopieren von',
        'script_is_privileged' => 'Privilegiert',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Erzwingt, dass ausgehender Netzwerkverkehr die Quell-IP auf die primäre Zuweisungs-IP des Servers NATed.',
        'features' => 'Zusätzliche Funktionen des Eggs. Nützlich zur Konfiguration weiterer Panel-Anpassungen.',
        'file_denylist' => 'Dateien, die vom Benutzer nicht bearbeitet werden sollten.',
        'script_is_privileged' => 'Installationsskript als privilegierten Container (root) ausführen.',
    ],

    'actions' => [
        'export' => 'Exportieren',
        'create' => 'Egg erstellen',
        'edit' => 'Bearbeiten',
    ],

    'notices' => [
        'cannot_delete' => 'Egg kann nicht gelöscht werden',
        'cannot_delete_body' => 'Dieses Egg ist mit :count Server(n) verbunden. Bitte löschen oder neu zuweisen.',
        'cannot_delete_multiple' => 'Eggs mit Servern können nicht gelöscht werden',
        'cannot_delete_multiple_body' => ':count Egg(s) sind mit Servern verbunden und wurden übersprungen.',
    ],

];
