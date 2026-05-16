<?php

return [
    'title' => 'Utilisateur',
    'exceptions' => [
        'delete_self' => 'Vous ne pouvez pas supprimer votre propre compte.',
        'user_has_servers' => 'Impossible de supprimer un utilisateur dont le compte est associé à des serveurs actifs. Veuillez supprimer ses serveurs avant de continuer.',
    ],
    'notices' => [
        'account_created' => 'Le compte a été créé avec succès.',
        'account_updated' => 'Le compte a été mis à jour avec succès.',
    ],
    'details' => [
        'account_details' => 'Détails du compte',
        'external_id' => 'ID externe',
        'username' => 'Nom d\'utilisateur',
        'email' => 'Adresse email',
        'first_name' => 'Prénom',
        'last_name' => 'Nom de famille',
        'language' => 'Langue',
        'geolocate' => 'Géolocaliser (automatique)',
        'password' => 'Mot de passe',
        'password_confirmation' => 'Confirmez le mot de passe',
        'root_admin' => 'Administrateur racine',
        'root_admin_desc' => 'Cet utilisateur aura un accès complet à tous les serveurs et paramètres du système.',
        'privileges' => 'Privilèges',
        'admin_status' => 'Statut d\'administrateur',
    ],
];
