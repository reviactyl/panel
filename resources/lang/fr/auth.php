<?php

return [
    'username-required' => 'Un nom \'utilisateur ou une adresse mail doit être fourni.',
    'password-required' => 'Veuillez saisir le mot de passe de votre compte.',
    'email-required' => 'Une adresse e-mail valide doit être fournie pour continuer.',

    'login-title' => 'Connectez-vous pour continuer',

    'username-label' => 'Nom d\'utilisateur ou adresse mail',
    'password-label' => 'Mot de passe',

    'login-button' => 'Connexion',
    'return' => 'Retourner à la connexion',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'This account has not been linked to any :provider account. Please log in with your email and password first, then link your :provider account in the Account Settings page.',
    ],

    'forgot-password' => [
        'title' => 'Demander une réinitialisation de mot de passe',
        'label' => 'Mot de passe oublié?',
        'email-label' => 'Adresse mail',
        'email-content' => 'Entrez l\'adresse mail associée à votre compte pour recevoir les instructions permettant de réinitialiser votre mot de passe.',
        'send-email' => 'Envoyer un mail',
    ],

    'checkpoint' => [
        'title' => 'Point de contrôle des appareils',
        'recovery-code' => 'Code de récupération',
        'auth-code' => 'Code d\'authentification',
        'is-missing' => 'Pour continuer, entrez l\'un des codes de récupération générés lors de la configuration de l\'authentification à deux facteurs sur ce compte.',
        'is-not-missing' => 'Entrez le jeton à deux facteurs généré par votre appareil.',
        'button' => 'Continuer',
        'lost-device' => 'J\'ai perdu mon appareil',
        'not-lost-device' => 'J\'ai mon appareil',

    ],

    'reset-password' => [
        'new-required' => 'Un nouveau mot de passe est requis.',
        'min-required' => 'Votre nouveau mot de passe doit comporter au moins 8 caractères.',
        'no-match' => 'Votre nouveau mot de passe ne correspond pas.',
        'email-label' => 'Email',
        'new-label' => 'Nouveau mot de passe',
        'min-length' => 'Les mots de passe doivent comporter au moins 8 caractères.',
        'confirm-label' => 'Confirmer le nouveau mot de passe',
        'label' => 'Réinitialiser le mot de passe',
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
    
    'failed' => 'Aucun compte correspondant à ces informations d\'identification n\'a été trouvé.',

    'two_factor' => [
        'label' => 'Jeton à deux facteurs',
        'label_help' => 'Ce compte nécessite une deuxième étape d\'authentification pour continuer. Veuillez saisir le code généré par votre appareil pour terminer cette connexion.',
        'checkpoint_failed' => 'Le jeton d\'authentification à deux facteurs n\'était pas valide.',
    ],

    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',
    'password_requirements' => 'Le mot de passe doit comporter au moins 8 caractères et être unique à ce site.',
    '2fa_must_be_enabled' => 'L\'administrateur a exigé que l\'authentification à deux facteurs soit activée pour votre compte afin de pouvoir utiliser le panel.',
];
