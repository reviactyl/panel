<?php

return [

    'tabs' => [
        'configuration' => 'Ei-Konfiguration',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Konfiguration',
        ],
        'identity' => [
            'title' => 'Identität',
        ],
        'docker_images' => [
            'title' => 'Docker-Bilder',
            'description' => 'Die Docker-Images, die Servern zur Verfügung stehen, die dieses Ei verwenden. Geben Sie eins pro Zeile ein.',
        ],
        'process_management' => [
            'title' => 'Prozessmanagement',
        ],
        'variables' => [
            'title' => 'Variablen',
        ],
        'install_script' => [
            'title' => 'Skript installieren',
        ],
    ],

    'fields' => [
        'nest' => 'Nest',
        'uuid' => 'UUID',
        'name' => 'Name',
        'author' => 'Autor',
        'image' => 'Bild',
        'description' => 'Beschreibung',
        'image_name' => 'Bildname',
        'image_uri' => 'Bild-URI',
        'add_docker_image' => 'Docker-Image hinzufügen',
        'force_outgoing_ip' => 'Ausgehende IP erzwingen',
        'features' => 'Merkmale',
        'startup' => 'Startbefehl',
        'config_stop' => 'Stoppbefehl',
        'config_from' => 'Einstellungen kopieren von',
        'config_startup' => 'Startkonfiguration (JSON)',
        'config_logs' => 'Protokollkonfiguration (JSON)',
        'config_files' => 'Konfigurationsdateien (JSON)',
        'file_denylist' => 'Dateiverweigererliste',
        'env_variable' => 'Umgebungsvariable',
        'user_viewable' => 'Benutzer können anzeigen',
        'user_editable' => 'Benutzer können bearbeiten',
        'rules' => 'Eingaberegeln',
        'default_value' => 'Standardwert',
        'script_install' => 'Skript installieren',
        'script_container' => 'Skriptcontainer',
        'script_entry' => 'Skript-Entrypoint-Befehl',
        'copy_script_from' => 'Skript kopieren von',
        'script_is_privileged' => 'Privilegiert',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Erzwingt, dass die Quell-IP des gesamten ausgehenden Netzwerkverkehrs über NAT mit der IP der primären Zuweisungs-IP des Servers verbunden wird.',
        'features' => 'Zusätzliche Merkmale, die zum Ei gehören. Nützlich für die Konfiguration zusätzlicher Panel-Modifikationen.',
        'file_denylist' => 'Dateien, die vom Benutzer nicht bearbeitet werden sollten.',
        'script_is_privileged' => 'Führen Sie das Installationsskript als privilegierter Container (root) aus.',
    ],

    'actions' => [
        'export' => 'Export',
        'create' => 'Erstelle ein Ei',
        'edit' => 'Bearbeiten',
    ],

    'notices' => [
        'cannot_delete' => 'Ei kann nicht gelöscht werden',
        'cannot_delete_body' => 'Mit diesem Ei sind :count-Server verknüpft. Bitte löschen Sie sie zuerst oder weisen Sie sie neu zu.',
        'cannot_delete_multiple' => 'Eier mit Servern können nicht gelöscht werden',
        'cannot_delete_multiple_body' => ':count-Eier haben zugeordnete Server und wurden übersprungen.',
    ],

];
