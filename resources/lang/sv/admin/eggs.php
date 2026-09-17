<?php

return [

    'tabs' => [
        'configuration' => 'Äggkonfiguration',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Konfiguration',
        ],
        'identity' => [
            'title' => 'Identitet',
        ],
        'docker_images' => [
            'title' => 'Docker-bilder',
            'description' => 'Docker-bilderna tillgängliga för servrar som använder detta ägg. Ange en per rad.',
        ],
        'process_management' => [
            'title' => 'Process Management',
        ],
        'variables' => [
            'title' => 'Variabler',
        ],
        'install_script' => [
            'title' => 'Installera script',
        ],
    ],

    'fields' => [
        'nest' => 'Bo',
        'uuid' => 'UUID',
        'name' => 'Namn',
        'author' => 'Författare',
        'image' => 'Bild',
        'description' => 'Beskrivning',
        'image_name' => 'Bildens namn',
        'image_uri' => 'Bild-URI',
        'add_docker_image' => 'Lägg till Docker Image',
        'force_outgoing_ip' => 'Tvinga utgående IP',
        'features' => 'Drag',
        'startup' => 'Startkommando',
        'config_stop' => 'Stoppkommando',
        'config_from' => 'Kopiera inställningar från',
        'config_startup' => 'Starta konfiguration (JSON)',
        'config_logs' => 'Loggkonfiguration (JSON)',
        'config_files' => 'Konfigurationsfiler (JSON)',
        'file_denylist' => 'Filavvisningslista',
        'env_variable' => 'Miljövariabel',
        'user_viewable' => 'Användare kan se',
        'user_editable' => 'Användare kan redigera',
        'rules' => 'Inmatningsregler',
        'default_value' => 'Standardvärde',
        'script_install' => 'Installera script',
        'script_container' => 'Skriptbehållare',
        'script_entry' => 'Script Entrypoint-kommando',
        'copy_script_from' => 'Kopiera skript från',
        'script_is_privileged' => 'Privilegierad',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Tvingar all utgående nätverkstrafik att få sin käll-IP NATerad till IP:n för serverns primära allokerings-IP.',
        'features' => 'Ytterligare funktioner som hör till ägget. Användbar för att konfigurera ytterligare paneländringar.',
        'file_denylist' => 'Filer som inte ska redigeras av användaren.',
        'script_is_privileged' => 'Kör installationsskriptet som en privilegierad behållare (root).',
    ],

    'actions' => [
        'export' => 'Exportera',
        'create' => 'Skapa ägg',
        'edit' => 'Redigera',
    ],

    'notices' => [
        'cannot_delete' => 'Kan inte ta bort ägg',
        'cannot_delete_body' => 'Detta ägg har :count-server(ar) associerade. Ta bort eller tilldela dem först.',
        'cannot_delete_multiple' => 'Det går inte att ta bort ägg med servrar',
        'cannot_delete_multiple_body' => ':count-ägg har associerade servrar och hoppades över.',
    ],

];
