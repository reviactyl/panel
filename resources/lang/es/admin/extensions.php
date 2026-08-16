<?php

return [

    'label' => 'Extensión',
    'plural-label' => 'Extensiones',
    'marketplace_heading' => 'Available Extensions',

    'columns' => [
        'icon' => 'Icon',
        'id' => 'ID',
        'name' => 'Nombre',
        'version' => 'Versión',
        'author' => 'Autor',
        'enabled' => 'Activado',
        'updated' => 'Actualizado',
        'last_updated' => 'Last Updated',
        'downloads' => 'Downloads',
        'manifest_json' => 'JSON manifiesto',
        'file' => 'Choose a .rext file to upload',
    ],

    'modals' => [
        'manifest' => 'Manifiesto de extensión',
    ],

    'actions' => [
        'edit' => 'Editar',
        'view' => 'Get Extension',
        'upload' => 'Subir',
        'manifest' => 'Ver manifiesto',
        'disable' => 'Desactivar',
        'enable' => 'Permitir',
        'delete' => 'Borrar',
        'close' => 'Cerca',
    ],

    'alerts' => [
        'enabled' => 'Extensión habilitada.',
        'enable_failed' => 'No se pudo habilitar la extensión.',
        'disabled' => 'Extensión deshabilitada.',
        'disable_failed' => 'No se pudo deshabilitar la extensión.',
        'uninstalled' => 'Extensión desinstalada.',
        'uninstall_failed' => 'No se pudo desinstalar la extensión.',
        'could_not_locate_file' => 'No se pudo localizar el archivo del paquete cargado.',
        'invalid_file_type' => 'Sólo se permiten archivos .rext.',
        'upload_hint' => 'Sólo se permiten paquetes de extensión .rext.',
        'install_failed' => 'La instalación de la extensión falló.',
        'install_success' => 'Se instaló :name (:version) con éxito.',
    ],

];
