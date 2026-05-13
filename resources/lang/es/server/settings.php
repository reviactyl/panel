<?php

return [
    'title' => 'Configuración',
    'sftp' => [
        'title' => 'Detalles SFTP',
        'address' => 'Dirección del servidor',
        'username' => 'Nombre de usuario',
        'password' => 'Tu contraseña SFTP es la misma que utilizas para acceder a este panel.',
        'button' => 'Abrir SFTP',
    ],
    'info' => [
        'title' => 'Información de depuración',
        'node' => 'Nodo',
        'server' => 'ID del servidor',
        'public-status-page' => 'Página de estado público',
    ],
    'rename' => [
        'title' => 'Cambiar detalles del servidor',
        'name' => 'Nombre del servidor',
        'description' => 'Descripción del servidor',
        'button' => 'Guardar',
    ],
    'reinstall' => [
        'title' => 'Reinstalar servidor',
        'confirm-title' => 'Confirmar reinstalación del servidor',
        'confirm' => 'Sí, reinstalar servidor',
        'info' => 'Tu servidor será detenido y algunos archivos pueden ser eliminados o modificados durante este proceso, ¿estás seguro de que deseas continuar?',
        'info-1' => 'Reinstalar tu servidor lo detendrá y luego volverá a ejecutar el script de instalación que lo configuró inicialmente.',
        'info-2' => 'Algunos archivos pueden ser eliminados o modificados durante este proceso, por favor realiza una copia de seguridad de tus datos antes de continuar.',
        'button' => 'Reinstalar servidor',
    ],
];
