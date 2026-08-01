<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Usuario del sistema',
        'system' => 'Sistema',
        'using-api-key' => 'Usando clave API',
        'using-sftp' => 'Usando SFTP',
    ],
    'auth' => [
        'fail' => 'Inicio de sesión fallido',
        'success' => 'Sesión iniciada',
        'password-reset' => 'Contraseña restablecida',
        'reset-password' => 'Solicitado restablecimiento de contraseña',
        'checkpoint' => 'Se solicitó autenticación de doble factor',
        'recovery-token' => 'Usó token de recuperación de doble factor',
        'token' => 'Superó desafío de doble factor',
        'ip-blocked' => 'Solicitud bloqueada desde IP no listada para :identifier',
        'sftp' => [
            'fail' => 'Inicio de sesión SFTP fallido',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'Correo cambiado de :old a :new',
            'password-changed' => 'Contraseña cambiada',
            'language-changed' => 'Correo cambiado de :old a :new',
        ],
        'api-key' => [
            'create' => 'Creada nueva clave API :identifier',
            'delete' => 'Eliminada clave API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Añadida clave SSH :fingerprint a la cuenta',
            'delete' => 'Eliminada clave SSH :fingerprint de la cuenta',
        ],
        'two-factor' => [
            'create' => 'Autenticación de doble factor habilitada',
            'delete' => 'Autenticación de doble factor deshabilitada',
        ],
    ],
    'server' => [
        'reinstall' => 'Servidor reinstalado',
        'console' => [
            'command' => 'Ejecutado ":command" en el servidor',
        ],
        'power' => [
            'start' => 'Servidor iniciado',
            'stop' => 'Servidor detenido',
            'restart' => 'Servidor reiniciado',
            'kill' => 'Proceso del servidor finalizado',
        ],
        'backup' => [
            'download' => 'Copia de seguridad :name descargada',
            'delete' => 'Copia de seguridad :name eliminada',
            'restore' => 'Copia de seguridad :name restaurada (archivos eliminados: :truncate)',
            'restore-complete' => 'Restauración de la copia de seguridad :name completada',
            'restore-failed' => 'No se pudo completar la restauración de la copia de seguridad :name',
            'start' => 'Iniciada nueva copia de seguridad :name',
            'complete' => 'Copia de seguridad :name marcada como completa',
            'fail' => 'Copia de seguridad :name marcada como fallida',
            'lock' => 'Copia de seguridad :name bloqueada',
            'unlock' => 'Copia de seguridad :name desbloqueada',
        ],
        'database' => [
            'create' => 'Creada nueva base de datos :name',
            'rotate-password' => 'Contraseña rotada para la base de datos :name',
            'delete' => 'Base de datos :name eliminada',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => 'Comprimidos :count archivos en :directory',
            'read' => 'Visto el contenido de :file',
            'copy' => 'Creada una copia de :file',
            'create-directory' => 'Directorio :directory:name creado',
            'decompress' => 'Descomprimidos :files en :directory',
            'delete_one' => 'Archivo :directory:files.0 eliminado',
            'delete_other' => 'Eliminados :count archivos en :directory',
            'download' => 'Archivo :file descargado',
            'pull' => 'Archivo remoto descargado desde :url a :directory',
            'rename_one' => 'Renombrado :directory:files.0.from a :directory:files.0.to',
            'rename_other' => 'Renombrados :count archivos en :directory',
            'write' => 'Escrito nuevo contenido en :file',
            'upload' => 'Subiendo archivo',
            'uploaded' => 'Archivo :directory:file subido',
        ],
        'sftp' => [
            'denied' => 'Acceso SFTP bloqueado por permisos',
            'create_one' => 'Archivo :files.0 creado',
            'create_other' => 'Creados :count archivos nuevos',
            'write_one' => 'Contenido de :files.0 modificado',
            'write_other' => 'Contenido de :count archivos modificado',
            'delete_one' => 'Archivo :files.0 eliminado',
            'delete_other' => 'Eliminados :count archivos',
            'create-directory_one' => 'Directorio :files.0 creado',
            'create-directory_other' => 'Creados :count directorios',
            'rename_one' => 'Renombrado :files.0.from a :files.0.to',
            'rename_other' => 'Renombrados o movidos :count archivos',
        ],
        'allocation' => [
            'create' => 'Asignación :allocation añadida al servidor',
            'notes' => 'Notas de :allocation actualizadas de ":old" a ":new"',
            'primary' => 'Asignación :allocation establecida como principal del servidor',
            'delete' => 'Asignación :allocation eliminada',
        ],
        'schedule' => [
            'create' => 'Programación :name creada',
            'update' => 'Programación :name actualizada',
            'execute' => 'Programación :name ejecutada manualmente',
            'delete' => 'Programación :name eliminada',
        ],
        'task' => [
            'create' => 'Tarea ":action" creada para la programación :name',
            'update' => 'Tarea ":action" actualizada para la programación :name',
            'delete' => 'Tarea eliminada para la programación :name',
        ],
        'settings' => [
            'rename' => 'Servidor renombrado de :old a :new',
            'description' => 'Descripción del servidor cambiada de :old a :new',
        ],
        'startup' => [
            'edit' => 'Variable :variable cambiada de ":old" a ":new"',
            'image' => 'Imagen de Docker del servidor actualizada de :old a :new',
        ],
        'subuser' => [
            'create' => 'Usuario :email añadido como subusuario',
            'update' => 'Permisos del subusuario :email actualizados',
            'delete' => 'Subusuario :email eliminado',
        ],
    ],
];
