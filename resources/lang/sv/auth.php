<?php

return [
    'username-required' => 'Ett användarnamn eller e-post måste anges',
    'password-required' => 'Vänligen ange ditt kontolösenord',
    'email-required' => 'En giltig e-postadress måste anges för att fortsätta',

    'login-title' => 'Logga in för att fortsätta',

    'username-label' => 'Användarnamn eller E-post',
    'password-label' => 'Lösenord',

    'login-button' => 'Logga in',
    'return' => 'Återgå till inloggning',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'Detta konto har inte kopplats till något :provider-konto. Logga in med din e-postadress och ditt lösenord först och koppla sedan ditt :provider-konto på sidan Kontoinställningar.',
    ],

    'forgot-password' => [
        'title' => 'Begär återställning av lösenord',
        'label' => 'Glömt lösenord?',
        'email-label' => 'E-post',
        'email-content' => 'Ange din kontos e-postadress för att få instruktioner om återställning av ditt lösenord',
        'send-email' => 'Skicka e-post',
    ],

    'checkpoint' => [
        'title' => 'Enhetskontrollpunkt',
        'recovery-code' => 'Återställningskod',
        'auth-code' => 'Autentiseringskod',
        'is-missing' => 'Ange en av återställningskoderna som genererades när du ställde in tvåfaktorsautentisering på detta konto för att fortsätta',
        'is-not-missing' => 'Ange den tvåfaktortoken som genererats av din enhe',
        'button' => 'Fortsätt',
        'lost-device' => 'Jag har tappat bort min enhet',
        'not-lost-device' => 'Jag har min enhet',

    ],

    'reset-password' => [
        'new-required' => 'Ett nytt lösenord krävs',
        'min-required' => 'Ditt nya lösenord bör vara minst 8 tecken långt',
        'no-match' => 'Ditt nya lösenord matchar inte',
        'email-label' => 'E-post',
        'new-label' => 'Nytt lösenord',
        'min-length' => 'Lösenord måste vara minst 8 tecken långa.',
        'confirm-label' => 'Bekräfta nytt lösenord',
        'label' => 'Återställ lösenord',
    ],

    'register' => [
        'no-match' => 'Ditt lösenord stämmer inte överens.',
        'namefirst-label' => 'Förnamn',
        'namelast-label' => 'Efternamn',
        'email-label' => 'E-post',
        'username-label' => 'Användarnamn',
        'password-label' => 'Lösenord',
        'min-length' => 'Lösenord måste vara minst 8 tecken långt.',
        'confirm-label' => 'Bekräfta lösenord',
        'label' => 'Registrera',
    ],
    
    'failed' => 'Ingen matchande konto med dessa uppgifter kunde hittas.',

    'two_factor' => [
        'label' => 'Tvåfaktortoken',
        'label_help' => 'Detta konto kräver ett andra lager av autentisering för att kunna logga in. Vänligen ange koden som genererats av din enhet för att slutföra denna inloggning.',
        'checkpoint_failed' => 'Tvåfaktorsautentiseringstoken var ogiltig.',
    ],

    'throttle' => 'För många inloggningsförsök. Vänligen försök igen om :seconds sekunder.',
    'password_requirements' => 'Lösenordet måste vara minst 8 tecken långt och bör vara unikt för denna webbplats.',
    '2fa_must_be_enabled' => 'Administratören har krävt att tvåfaktorsautentisering aktiveras för ditt konto för att kunna använda panelen.',
];
