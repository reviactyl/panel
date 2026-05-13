<?php

return [

    'label' => 'Extension',
    'plural-label' => 'Rallonges',

    'columns' => [
        'id' => 'ID',
        'name' => 'Nom',
        'version' => 'Version',
        'author' => 'Auteur',
        'enabled' => 'Activé',
        'updated' => 'Mis à jour',
        'manifest_json' => 'Manifeste JSON',
    ],

    'modals' => [
        'manifest' => 'Manifeste d\'extension',
    ],

    'actions' => [
        'edit' => 'Modifier',
        'upload' => 'Télécharger',
        'manifest' => 'Afficher le manifeste',
        'disable' => 'Désactiver',
        'enable' => 'Activer',
        'delete' => 'Supprimer',
        'close' => 'Fermer',
    ],

    'alerts' => [
        'enabled' => 'Extension activée.',
        'enable_failed' => 'Échec de l\'activation de l\'extension.',
        'disabled' => 'Poste désactivé.',
        'disable_failed' => 'Échec de la désactivation de l\'extension.',
        'uninstalled' => 'Extension désinstallée.',
        'uninstall_failed' => 'Échec de la désinstallation de l\'extension.',
        'could_not_locate_file' => 'Impossible de localiser le fichier du package téléchargé.',
        'invalid_file_type' => 'Seuls les fichiers .rext sont autorisés.',
        'upload_hint' => 'Seuls les packages d\'extension .rext sont autorisés.',
        'install_failed' => 'L\'installation de l\'extension a échoué.',
        'install_success' => ':name (:version) installé avec succès.',
    ],

];
