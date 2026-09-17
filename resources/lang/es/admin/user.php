<?php

return [
    'title' => 'Usuario',
    'exceptions' => [
        'delete_self' => 'No puedes eliminar tu propia cuenta.',
        'user_has_servers' => 'No se puede eliminar un usuario con servidores activos vinculados a su cuenta. Por favor elimina sus servidores antes de continuar.',
    ],
    'notices' => [
        'account_created' => 'La cuenta ha sido creada exitosamente.',
        'account_updated' => 'La cuenta ha sido actualizada exitosamente.',
    ],
    'details' => [
        'account_details' => 'Detalles de la cuenta',
        'external_id' => 'Identificación externa',
        'username' => 'Nombre de usuario',
        'email' => 'Dirección de correo electrónico',
        'first_name' => 'Nombre de pila',
        'last_name' => 'Apellido',
        'language' => 'Idioma',
        'geolocate' => 'Geolocalizar (Automático)',
        'password' => 'Contraseña',
        'password_confirmation' => 'confirmar Contraseña',
        'root_admin' => 'Administrador raíz',
        'root_admin_desc' => 'Este usuario tendrá acceso completo a todos los servidores y configuraciones del sistema.',
        'privileges' => 'Privilegios',
        'admin_status' => 'Estado de administrador',
    ],
];
