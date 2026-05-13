<?php

return [
    
    'label' => 'Nest',
    'plural_label' => 'Nests',

    'sections' => [
        'configuration' => 'Nest-Konfiguration',
    ],

    'fields' => [
        'name' => 'Name',
        'author' => 'Autor',
        'description' => 'Beschreibung',
    ],

    'helpers' => [
        'name' => 'Ein eindeutiger Name, um dieses Nest zu identifizieren.',
        'author' => 'Der Autor dieses Nests. Muss eine gültige E-Mail-Adresse sein.',
        'description' => 'Eine Beschreibung dieses Nests.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'author' => 'Autor',
        'eggs' => 'Eggs',
        'servers' => 'Server',
    ],
    
    'actions' => [
        'import' => 'Egg importieren',
    ],

    'import' => [
        'file_label' => 'Egg-Datei (JSON)',
        'nest_label' => 'Zugehöriges Nest',
        'file_not_found' => 'Datei nicht gefunden',
        'file_not_found_body' => 'Die hochgeladene Datei konnte nicht gefunden werden.',
        'invalid_format' => 'Ungültiges Dateiformat',
        'invalid_format_body' => 'Unerwartetes Dateiformat erhalten.',
        'success' => 'Egg erfolgreich importiert',
        'failed' => 'Import des Eggs fehlgeschlagen',
    ],

    'notices' => [
        'created' => 'Ein neues Nest, :name, wurde erfolgreich erstellt.',
        'deleted' => 'Das angeforderte Nest wurde erfolgreich aus dem Panel gelöscht.',
        'updated' => 'Die Nest-Konfigurationsoptionen wurden erfolgreich aktualisiert.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Dieses Egg und seine zugehörigen Variablen wurden erfolgreich importiert.',
            'updated_via_import' => 'Dieses Egg wurde mit der bereitgestellten Datei aktualisiert.',
            'deleted' => 'Das angeforderte Egg wurde erfolgreich aus dem Panel gelöscht.',
            'updated' => 'Die Egg-Konfiguration wurde erfolgreich aktualisiert.',
            'script_updated' => 'Das Installationsskript des Eggs wurde aktualisiert und wird bei jeder Serverinstallation ausgeführt.',
            'egg_created' => 'Ein neues Egg wurde erfolgreich erstellt. Sie müssen alle laufenden Daemons neu starten, um dieses neue Egg anzuwenden.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'Die Variable ":variable" wurde gelöscht und steht Servern nach einem Rebuild nicht mehr zur Verfügung.',
            'variable_updated' => 'Die Variable ":variable" wurde aktualisiert. Sie müssen alle Server, die diese Variable verwenden, neu erstellen, um die Änderungen zu übernehmen.',
            'variable_created' => 'Neue Variable wurde erfolgreich erstellt und diesem Egg zugewiesen.',
        ],
    ],
];
