<?php

return [

    'label' => 'Nest',
    'plural_label' => 'Nester',

    'sections' => [
        'configuration' => 'Nest Konfiguration',
    ],

    'fields' => [
        'name' => 'Name',
        'author' => 'Ersteller',
        'description' => 'Beschreibung',
    ],

    'helpers' => [
        'name' => 'Ein eindeutiger Name um dieses Nest zu identifizieren.',
        'author' => 'Der Ersteller dieses Nests. Muss eine gültige E-Mail sein.',
        'description' => 'Eine Beschreibung dieses Nests.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'author' => 'Ersteller',
        'eggs' => 'Eier',
        'servers' => 'Server',
    ],

    'actions' => [
        'import' => 'Ei importieren',
    ],

    'import' => [
        'file_label' => 'Egg-Datei (JSON)',
        'nest_label' => 'Zugehöriges Nest',
        'file_not_found' => 'Datei nicht gefunden',
        'file_not_found_body' => 'Die hochgeladene Datei konnte nicht gefunden werden.',
        'invalid_format' => 'Ungültiges Dateiformat',
        'invalid_format_body' => 'Unerwartetes Dateiformat empfangen.',
        'success' => 'Ei erfolgreich importiert',
        'failed' => 'Ei konnte nicht importiert werden',
    ],

    'notices' => [
        'created' => 'Ein neues Nest, :name, wurde erfolgreich erstellt.',
        'deleted' => 'Das angeforderte Nest wurde erfolgreich aus dem Panel gelöscht.',
        'updated' => 'Die Nest-Konfigurationsoptionen wurden erfolgreich aktualisiert.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Dieses Ei und seine zugehörigen Variablen wurden erfolgreich importiert.',
            'updated_via_import' => 'Dieses Ei wurde unter Verwendung der bereitgestellten Datei aktualisiert.',
            'deleted' => 'Das angeforderte Ei wurde erfolgreich aus dem Panel gelöscht.',
            'updated' => 'Die Ei-Konfiguration wurde erfolgreich aktualisiert.',
            'script_updated' => 'Das Ei-Installationsskript wurde aktualisiert und wird ausgeführt, wann immer Server installiert werden.',
            'egg_created' => 'Ein neues Ei wurde erfolgreich gelegt. Sie müssen alle laufenden Daemons neu starten, um dieses neue Ei anzuwenden.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'Die Variable ":variable" wurde gelöscht und steht Servern nach einem Rebuild nicht mehr zur Verfügung.',
            'variable_updated' => 'Die Variable ":variable" wurde aktualisiert. Sie müssen alle Server, die diese Variable verwenden, neu erstellen, um Änderungen zu übernehmen.',
            'variable_created' => 'Neue Variable wurde erfolgreich erstellt und diesem Ei zugewiesen.',
        ],
    ],
];
