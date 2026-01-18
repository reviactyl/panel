<?php

return [
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