<?php

return [

    'label' => 'Nest',
    'plural_label' => 'Nesten',

    'sections' => [
        'configuration' => 'Nestkonfiguration',
    ],

    'fields' => [
        'name' => 'Namn',
        'author' => 'Författare',
        'description' => 'Beskrivning',
    ],

    'helpers' => [
        'name' => 'Ett unikt namn som används för att identifiera detta nest.',
        'author' => 'Författaren till detta nest. Måste vara en giltig e-postadress.',
        'description' => 'En beskrivning av detta nest.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Namn',
        'author' => 'Författare',
        'eggs' => 'Ägg',
        'servers' => 'Servrar',
    ],

    'actions' => [
        'import' => 'Import Egg',
    ],

    'import' => [
        'file_label' => 'Egg File (JSON)',
        'nest_label' => 'Associated Nest',
        'file_not_found' => 'File not found',
        'file_not_found_body' => 'Could not locate uploaded file.',
        'invalid_format' => 'Invalid file format',
        'invalid_format_body' => 'Unexpected file format received.',
        'success' => 'Egg imported successfully',
        'failed' => 'Failed to import egg',
    ],

    'notices' => [
        'created' => 'Ett nytt näste, :name, har skapats framgångsrikt.',
        'deleted' => 'Det begärda nästet har tagits bort från panelen.',
        'updated' => 'Nästets konfigurationsalternativ har uppdaterats framgångsrikt.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Detta Egg och dess associerade variabler har importerats framgångsrikt.',
            'updated_via_import' => 'Detta Egg har uppdaterats med den tillhandahållna filen.',
            'deleted' => 'Det begärda ägget har tagits bort från panelen.',
            'updated' => 'Egg-konfigurationen har uppdaterats framgångsrikt.',
            'script_updated' => 'Egg-installationsskriptet har uppdaterats och kommer att köras när servrar installeras.',
            'egg_created' => 'Ett nytt ägg skapades framgångsrikt. Du behöver starta om alla körande daemoner för att tillämpa detta nya ägg.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'Variabeln ":variable" har tagits bort och kommer inte längre att vara tillgänglig för servrar efter återuppbyggnad.',
            'variable_updated' => 'Variabeln ":variable" har uppdaterats. Du behöver återuppbygga alla servrar som använder denna variabel för att tillämpa ändringar.',
            'variable_created' => 'Ny variabel har skapats framgångsrikt och tilldelats detta ägg.',
        ],
    ],
];
