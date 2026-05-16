<?php

return [

    'label' => 'Nido',
    'plural_label' => 'Nidos',

    'sections' => [
        'configuration' => 'Configuración de nido',
    ],

    'fields' => [
        'name' => 'Nombre',
        'author' => 'Autor',
        'description' => 'Descripción',
    ],

    'helpers' => [
        'name' => 'Un nombre único utilizado para identificar este nido.',
        'author' => 'El autor de este nido. Debe ser un correo electrónico válido.',
        'description' => 'Una descripción de este nido.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'author' => 'Autor',
        'eggs' => 'Huevos',
        'servers' => 'Servidores',
    ],

    'actions' => [
        'import' => 'Importar huevo',
    ],

    'import' => [
        'file_label' => 'Archivo de huevo (JSON)',
        'nest_label' => 'Nido asociado',
        'file_not_found' => 'Archivo no encontrado',
        'file_not_found_body' => 'No se pudo localizar el archivo cargado.',
        'invalid_format' => 'Formato de archivo no válido',
        'invalid_format_body' => 'Se recibió un formato de archivo inesperado.',
        'success' => 'Huevo importado con éxito',
        'failed' => 'No se pudo importar el huevo',
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
