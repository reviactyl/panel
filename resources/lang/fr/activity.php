<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Utilisateur Système',
        'system' => 'Système',
        'using-api-key' => 'Utilisation de la clé API',
        'using-sftp' => 'Utilisation du SFTP',
    ],
    'auth' => [
        'fail' => 'Échec de connexion',
        'success' => 'Connexion',
        'password-reset' => 'Réinitialiser le mot de passe',
        'reset-password' => 'Réinitialisation du mot de passe demandée',
        'checkpoint' => 'Authentification à deux facteurs requise',
        'recovery-token' => 'Jeton de récupération à deux facteurs utilisé',
        'token' => 'Défi à deux facteurs résolu',
        'ip-blocked' => 'Demande bloquée depuis une adresse IP non répertoriée pour :identifier',
        'sftp' => [
            'fail' => 'Échec de la connexion SFTP',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'Modification de l\'adresse mail de :old à :new',
            'password-changed' => 'Mot de passe modifié',
            'language-changed' => 'Changement de langue de :old à :new',
        ],
        'api-key' => [
            'create' => 'Création d\'une nouvelle clé API :identifier',
            'delete' => 'Suppression de la clé API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Ajout d\'une nouvelle clé SSH :fingerprint au compte',
            'delete' => 'Suppression de la clé SSH :fingerprint du compte',
        ],
        'two-factor' => [
            'create' => 'Authentification à deux facteurs activée',
            'delete' => 'Authentification à deux facteurs désactivée',
        ],
    ],
    'server' => [
        'reinstall' => 'Serveur réinstallé',
        'console' => [
            'command' => 'Execution de ":command" sur le serveur',
        ],
        'power' => [
            'start' => 'Démarrage du serveur',
            'stop' => 'Arrêt du serveur',
            'restart' => 'Redémarrage du serveur',
            'kill' => 'Arrêt forcé du serveur',
        ],
        'backup' => [
            'download' => 'Téléchargement de la sauvegarde :name',
            'delete' => 'Suppression de la sauvegarde :name',
            'restore' => 'Restauration de la sauvegarde :name (fichiers supprimés: :truncate)',
            'restore-complete' => 'Restauration de la sauevgarde :name terminée',
            'restore-failed' => 'Restauration de la sauvegarde :name échouée ',
            'start' => 'Lancement de la sauvegarde :name',
            'complete' => 'Sauvegarde :name terminée',
            'fail' => 'Sauvegarde :name échouée',
            'lock' => 'Sauvegarde :name verouillée',
            'unlock' => 'Sauvegarde :name déverouillée',
        ],
        'database' => [
            'create' => 'Création de la base de données :name',
            'rotate-password' => 'Rotation du mot de passe pour la base de données :name',
            'delete' => 'Suppression de la base de données :name',
        ],
        'file' => [
            'compress_one' => 'Compression de :directory:file',
            'compress_other' => 'Compression de :count fichier(s) dans :directory',
            'read' => 'Lecture du fichier :file',
            'copy' => 'Copie du fichier :file',
            'create-directory' => 'Création d\'un dossier :directory:name',
            'decompress' => 'Extraction de :files dans :directory',
            'delete_one' => 'Suppression de :directory:files.0',
            'delete_other' => 'Suppression de :count fichier(s) dans :directory',
            'download' => 'Téléchargement de :file',
            'pull' => 'Téléchargement d\'un fichier distant depuis :url dans :directory',
            'rename_one' => 'Renommage de :directory:files.0.from à :directory:files.0.to',
            'rename_other' => 'Renommage de :count fichier(s) dans :directory',
            'write' => 'Écriture dans :file',
            'upload' => 'Début du téléversement',
            'uploaded' => ':directory:file téléversé',
        ],
        'sftp' => [
            'denied' => 'Accès SFTP bloqué en raison des autorisations',
            'create_one' => 'Création de :files.0',
            'create_other' => 'Création de :count nouveaux fichiers',
            'write_one' => 'Contenu du fichier :files.0 modifié',
            'write_other' => 'Contenu de :count fichiers modifiés',
            'delete_one' => 'Suppression de :files.0',
            'delete_other' => 'Suppression :count fichiers',
            'create-directory_one' => 'Création de :files.0 directory',
            'create-directory_other' => 'Création de :count dossiers',
            'rename_one' => 'Renommage de :files.0.from à :files.0.to',
            'rename_other' => 'Renommage ou déplacement de :count fichiers',
        ],
        'allocation' => [
            'create' => ':allocation ajoutée au serveur',
            'notes' => 'Mise à jour des notes pour :allocation de \':old\' à \':new\'',
            'primary' => 'Définir :allocation comme allocation principale du serveur',
            'delete' => 'Suppression de l\'allocation :allocation',
        ],
        'schedule' => [
            'create' => 'Création du planning :name',
            'update' => 'Modification de la tâche automatique : :name',
            'execute' => 'Éxecution manuelle de la tâche automatique :name',
            'delete' => 'Suppression de la tâche automatique :name',
        ],
        'task' => [
            'create' => 'Ajout d\'un ordre \':action\' pour la tâche automatique :name',
            'update' => 'Modification de l\'ordre \':action\' pour la tâche automatique :name',
            'delete' => 'Suppression d\'une tâche pour le planning :name',
        ],
        'settings' => [
            'rename' => 'Renommage du serveur de :old à :new',
            'description' => 'Modification de la description de :old à :new',
        ],
        'startup' => [
            'edit' => 'Modification de la variable :variable de \':old\' à \':new\'',
            'image' => 'Mise à jour de l\'image Docker pour le serveur de :old à :new',
        ],
        'subuser' => [
            'create' => 'Ajout de :email en tant que sous-utilisateur',
            'update' => 'Permissions mises à jour pour le sous-utilisateur :email',
            'delete' => 'Le sous-utilisateur :email a été supprimée',
        ],
    ],
];
