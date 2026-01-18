<?php

return [
    'username-required' => 'Ein Benutzername oder eine E-Mail muss angegeben werden.',
    'password-required' => 'Bitte geben Sie Ihr Kontopasswort ein.',
    'email-required' => 'Eine gültige E-Mail-Adresse muss angegeben werden, um fortzufahren.',

    'login-title' => 'Anmelden um fortzufahren',

    'username-label' => 'Benutzername oder E-Mail',
    'password-label' => 'Passwort',

    'login-button' => 'Anmelden',
    'return' => 'Zurück zur Anmeldung',

    'forgot-password' => [
        'title' => 'Passwort zurücksetzen anfordern',
        'label' => 'Passwort vergessen?',
        'email-label' => 'E-Mail',
        'email-content' => 'Geben Sie Ihre Konto-E-Mail-Adresse ein, um Anweisungen zum Zurücksetzen Ihres Passworts zu erhalten.',
        'send-email' => 'E-Mail senden',
    ],

    'checkpoint' => [
        'title' => 'Geräte-Checkpoint',
        'recovery-code' => 'Wiederherstellungscode',
        'auth-code' => 'Authentifizierungscode',
        'is-missing' => 'Geben Sie einen der Wiederherstellungscodes ein, die bei der Einrichtung der 2-Faktor-Authentifizierung für dieses Konto generiert wurden, um fortzufahren.',
        'is-not-missing' => 'Geben Sie den von Ihrem Gerät generierten Zwei-Faktor-Token ein.',
        'button' => 'Fortfahren',
        'lost-device' => 'Ich habe mein Gerät verloren',
        'not-lost-device' => 'Ich habe mein Gerät',

    ],

    'reset-password' => [
        'new-required' => 'Ein neues Passwort ist erforderlich.',
        'min-required' => 'Ihr neues Passwort sollte mindestens 8 Zeichen lang sein.',
        'no-match' => 'Ihr neues Passwort stimmt nicht überein.',
        'email-label' => 'E-Mail',
        'new-label' => 'Neues Passwort',
        'min-length' => 'Passwörter müssen mindestens 8 Zeichen lang sein.',
        'confirm-label' => 'Neues Passwort bestätigen',
        'label' => 'Passwort zurücksetzen',
    ],
    
    'failed' => 'Diese Kombination aus Zugangsdaten wurde nicht in unserer Datenbank gefunden.',

    'two_factor' => [
        'label' => '2-Faktor-Token',
        'label_help' => 'Dieses Konto erfordert eine zweite Authentifizierungsebene, um fortzufahren. Bitte geben Sie den von Ihrem Gerät generierten Code ein, um diese Anmeldung abzuschließen.',
        'checkpoint_failed' => 'Der Zwei-Faktor-Authentifizierungstoken war ungültig.',
    ],

    'throttle' => 'Zu viele Loginversuche. Versuchen Sie es bitte in :seconds Sekunden nochmal.',
    'password_requirements' => 'Das Passwort muss mindestens 8 Zeichen lang sein und sollte für diese Seite eindeutig sein.',
    '2fa_must_be_enabled' => 'Der Administrator hat festgelegt, dass die 2-Faktor-Authentifizierung für Ihr Konto aktiviert sein muss, um das Panel nutzen zu können.',
];