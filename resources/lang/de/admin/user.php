<?php

return [
    'title' => 'Benutzer',
    'exceptions' => [
        'delete_self' => 'Sie können Ihr eigenes Konto nicht löschen.',
        'user_has_servers' => 'Ein Benutzer mit aktiven Servern, die mit seinem Konto verknüpft sind, kann nicht gelöscht werden. Bitte löschen Sie dessen Server, bevor Sie fortfahren.',
    ],
    'notices' => [
        'account_created' => 'Konto wurde erfolgreich erstellt.',
        'account_updated' => 'Konto wurde erfolgreich aktualisiert.',
    ],
    'details' => [
        'account_details' => 'Kontodetails',
        'external_id' => 'Externe ID',
        'username' => 'Benutzername',
        'email' => 'E-Mail Adresse',
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'language' => 'Sprache',
        'geolocate' => 'Geolokalisieren (automatisch)',
        'password' => 'Passwort',
        'password_confirmation' => 'Passwort bestätigen',
        'root_admin' => 'Root-Administrator',
        'root_admin_desc' => 'Dieser Benutzer wird Vollzugriff auf alle Systeme und Einstellungen auf dem System bekommen.',
        'privileges' => 'Berechtigungen',
        'admin_status' => 'Admin-Status',
    ],
];
