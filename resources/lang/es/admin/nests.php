<?php

return [

    'label' => 'Nest',
    'plural_label' => 'Nests',

    'sections' => [
        'configuration' => 'Nest Configuration',
    ],

    'fields' => [
        'name' => 'Name',
        'author' => 'Author',
        'description' => 'Description',
    ],

    'helpers' => [
        'name' => 'A unique name used to identify this nest.',
        'author' => 'The author of this nest. Must be a valid email.',
        'description' => 'A description of this nest.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'author' => 'Author',
        'eggs' => 'Eggs',
        'servers' => 'Servers',
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
        'created' => 'Un nuevo nido, :name, ha sido creado exitosamente.',
        'deleted' => 'Se ha eliminado exitosamente el nido solicitado del Panel.',
        'updated' => 'Se han actualizado exitosamente las opciones de configuración del nido.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Se ha importado exitosamente este Egg y sus variables asociadas.',
            'updated_via_import' => 'Este Egg ha sido actualizado usando el archivo proporcionado.',
            'deleted' => 'Se ha eliminado exitosamente el egg solicitado del Panel.',
            'updated' => 'La configuración del Egg se ha actualizado exitosamente.',
            'script_updated' => 'El script de instalación del Egg ha sido actualizado y se ejecutará cada vez que se instalen servidores.',
            'egg_created' => 'Un nuevo egg fue creado exitosamente. Necesitarás reiniciar cualquier daemon en ejecución para aplicar este nuevo egg.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'La variable ":variable" ha sido eliminada y ya no estará disponible para los servidores una vez reconstruidos.',
            'variable_updated' => 'La variable ":variable" ha sido actualizada. Necesitarás reconstruir cualquier servidor que use esta variable para aplicar los cambios.',
            'variable_created' => 'Se ha creado exitosamente una nueva variable y se ha asignado a este egg.',
        ],
    ],
];
