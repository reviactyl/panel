<?php

return [

    'tabs' => [
        'configuration' => 'Configuration des œufs',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Configuration',
        ],
        'identity' => [
            'title' => 'Identité',
        ],
        'docker_images' => [
            'title' => 'Images Docker',
            'description' => 'Les images Docker disponibles pour les serveurs utilisant cet œuf. Entrez-en un par ligne.',
        ],
        'process_management' => [
            'title' => 'Gestion des processus',
        ],
        'variables' => [
            'title' => 'Variables',
        ],
        'install_script' => [
            'title' => 'Installer le script',
        ],
    ],

    'fields' => [
        'nest' => 'Nid',
        'uuid' => 'UUID',
        'name' => 'Nom',
        'author' => 'Auteur',
        'image' => 'Image',
        'description' => 'Description',
        'image_name' => 'Nom de l\'image',
        'image_uri' => 'URI de l’image',
        'add_docker_image' => 'Ajouter une image Docker',
        'force_outgoing_ip' => 'Forcer l\'adresse IP sortante',
        'features' => 'Caractéristiques',
        'startup' => 'Commande de démarrage',
        'config_stop' => 'Commande d\'arrêt',
        'config_from' => 'Copier les paramètres depuis',
        'config_startup' => 'Démarrer la configuration (JSON)',
        'config_logs' => 'Configuration du journal (JSON)',
        'config_files' => 'Fichiers de configuration (JSON)',
        'file_denylist' => 'Liste de refus de fichiers',
        'env_variable' => 'Variable d\'environnement',
        'user_viewable' => 'Les utilisateurs peuvent voir',
        'user_editable' => 'Les utilisateurs peuvent modifier',
        'rules' => 'Règles de saisie',
        'default_value' => 'Valeur par défaut',
        'script_install' => 'Installer le script',
        'script_container' => 'Conteneur de scripts',
        'script_entry' => 'Commande de point d\'entrée de script',
        'copy_script_from' => 'Copier le script depuis',
        'script_is_privileged' => 'Privilégié',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Force tout le trafic réseau sortant à avoir son adresse IP source NATée sur l\'adresse IP de l\'adresse IP d\'allocation principale du serveur.',
        'features' => 'Caractéristiques supplémentaires appartenant à l\'œuf. Utile pour configurer des modifications supplémentaires du panneau.',
        'file_denylist' => 'Fichiers qui ne doivent pas être modifiés par l\'utilisateur.',
        'script_is_privileged' => 'Exécutez le script d\'installation en tant que conteneur privilégié (root).',
    ],

    'actions' => [
        'export' => 'Exporter',
        'create' => 'Créer un œuf',
        'edit' => 'Modifier',
    ],

    'notices' => [
        'cannot_delete' => 'Impossible de supprimer l\'œuf',
        'cannot_delete_body' => 'Cet œuf est associé à un ou plusieurs serveurs :count. Veuillez d\'abord les supprimer ou les réaffecter.',
        'cannot_delete_multiple' => 'Impossible de supprimer les œufs avec les serveurs',
        'cannot_delete_multiple_body' => 'Les œufs :count ont des serveurs associés et ont été ignorés.',
    ],

];
