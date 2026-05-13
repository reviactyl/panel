<?php

return [
    'title' => 'Paramètres',
    'sftp' => [
        'title' => 'Informations du SFTP',
        'address' => 'Adresse du serveur',
        'username' => 'Identifiant',
        'password' => 'Votre mot de passe SFTP est le même que celui que vous utilisez pour accéder à ce panneau.',
        'button' => 'Lancer le SFTP',
    ],
    'info' => [
        'title' => 'Information de Debug',
        'node' => 'Nœud',
        'server' => 'Identifiant du serveur',
        'public-status-page' => 'Page d\'état publique',
    ],
    'rename' => [
        'title' => 'Modifier les détails du serveur',
        'name' => 'Nom du serveur',
        'description' => 'Description du serveur',
        'button' => 'Sauvegarder',
    ],
    'reinstall' => [
        'title' => 'Réinstaller le serveur',
        'confirm-title' => 'Confirmer la réinstallation du serveur',
        'confirm' => 'Je veux réinstaller le serveur',
        'info' => 'Votre serveur sera arrêté et certains fichiers pourraient être supprimés ou modifiés au cours de ce processus. Êtes-vous sûr de vouloir continuer ?',
        'info-1' => 'La réinstallation de votre serveur l\'arrêtera, puis relancera le script d\'installation qui l\'a initialement configuré.',
        'info-2' => 'Certains fichiers peuvent être supprimés ou modifiés au cours de ce processus, veuillez sauvegarder vos données avant de continuer.',
        'button' => 'Réinstaller le serveur',
    ],
];
