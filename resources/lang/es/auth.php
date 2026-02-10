<?php

return [
    'username-required' => 'Se debe proporcionar un nombre de usuario o correo electrónico.',
    'password-required' => 'Por favor, ingresa la contraseña de tu cuenta.',
    'email-required' => 'Se debe proporcionar una dirección de correo electrónico válida para continuar.',

    'login-title' => 'Iniciar sesión para continuar',

    'username-label' => 'Nombre de usuario o correo electrónico',
    'password-label' => 'Contraseña',

    'login-button' => 'Iniciar sesión',
    'return' => 'Volver al inicio de sesión',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'This account has not been linked to any :provider account. Please log in with your email and password first, then link your :provider account in the Account Settings page.',
    ],

    'forgot-password' => [
        'title' => 'Solicitar restablecimiento de contraseña',
        'label' => '¿Olvidaste tu contraseña?',
        'email-label' => 'Correo electrónico',
        'email-content' => 'Introduce el correo electrónico de tu cuenta para recibir instrucciones sobre cómo restablecer tu contraseña.',
        'send-email' => 'Enviar correo',
    ],

    'checkpoint' => [
        'title' => 'Verificación del dispositivo',
        'recovery-code' => 'Código de recuperación',
        'auth-code' => 'Código de autenticación',
        'is-missing' => 'Introduce uno de los códigos de recuperación generados al configurar la autenticación de doble factor en esta cuenta para continuar.',
        'is-not-missing' => 'Introduce el token de doble factor generado por tu dispositivo.',
        'button' => 'Continuar',
        'lost-device' => 'He perdido mi dispositivo',
        'not-lost-device' => 'Tengo mi dispositivo',

    ],

    'reset-password' => [
        'new-required' => 'Se requiere una nueva contraseña.',
        'min-required' => 'Tu nueva contraseña debe tener al menos 8 caracteres.',
        'no-match' => 'Tu nueva contraseña no coincide.',
        'email-label' => 'Correo electrónico',
        'new-label' => 'Nueva contraseña',
        'min-length' => 'Las contraseñas deben tener al menos 8 caracteres.',
        'confirm-label' => 'Confirmar nueva contraseña',
        'label' => 'Restablecer contraseña',
    ],

    'register' => [
        'no-match' => 'Your password does not match.',
        'namefirst-label' => 'First Name',
        'namelast-label' => 'Last Name',
        'email-label' => 'Email',
        'username-label' => 'UserName',
        'password-label' => 'Password',
        'min-length' => 'Passwords must be at least 8 characters in length.',
        'confirm-label' => 'Confirm Password',
        'label' => 'Register',
    ],
    
    'failed' => 'No se pudo encontrar ninguna cuenta que coincida con esas credenciales.',

    'two_factor' => [
        'label' => 'Token de 2-Factores',
        'label_help' => 'Esta cuenta requiere una segunda capa de autenticación para continuar. Por favor, ingresa el código generado por tu dispositivo para completar el inicio de sesión.',
        'checkpoint_failed' => 'El token de autenticación de doble factor no es válido.',
    ],

    'throttle' => 'Demasiados intentos de inicio de sesión. Por favor, intenta de nuevo en :seconds segundos.',
    'password_requirements' => 'La contraseña debe tener al menos 8 caracteres y ser única para este sitio.',
    '2fa_must_be_enabled' => 'El administrador ha requerido que la autenticación de 2 factores esté habilitada para tu cuenta para usar el Panel.',
];
