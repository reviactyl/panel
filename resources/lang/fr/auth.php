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
        'not_linked' => 'Ce compte n\'a été lié à aucun compte :provider. Veuillez d\'abord vous connecter avec votre e-mail et votre mot de passe, puis associer votre compte :provider sur la page Paramètres du compte.',
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
        'email-label' => 'E-mail',
        'new-label' => 'Nouveau mot de passe',
        'min-length' => 'Les mots de passe doivent comporter au moins 8 caractères.',
        'confirm-label' => 'Confirmer le nouveau mot de passe',
        'label' => 'Réinitialiser le mot de passe',
    ],

    'register' => [
        'no-match' => 'Votre mot de passe ne correspond pas.',
        'namefirst-label' => 'Prénom',
        'namelast-label' => 'Nom de famille',
        'email-label' => 'E-mail',
        'username-label' => 'Nom d\'utilisateur',
        'password-label' => 'Mot de passe',
        'min-length' => 'Les mots de passe doivent comporter au moins 8 caractères.',
        'confirm-label' => 'Confirmez le mot de passe',
        'label' => 'Registre',
        'create-account' => 'Créer un compte',
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
