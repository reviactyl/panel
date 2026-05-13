<?php

return [
    'label' => 'Serveur',
    'plural-label' => 'Serveurs',

    'sections' => [
        'identity' => [
            'title' => 'Identité',
            'description' => 'Informations de base sur le serveur et propriété.',
        ],
        'allocation' => [
            'title' => 'Allocation',
            'description' => 'Sélectionnez le nœud et l\'allocation réseau pour ce serveur.',
        ],
        'startup' => [
            'title' => 'Démarrer',
            'description' => 'Configurez l\'œuf, la commande de démarrage et l\'image Docker.',
        ],
        'resources' => [
            'title' => 'Limites des ressources',
            'description' => 'Définissez les limites des ressources du serveur.',
        ],
        'feature_limits' => [
            'title' => 'Limites des fonctionnalités',
            'description' => 'Limitez les bases de données, les allocations et les sauvegardes.',
        ],
        'environment' => [
            'title' => 'Variables d\'environnement',
            'description' => 'Définissez les valeurs d\'environnement pour l\'œuf sélectionné.',
        ],
    ],

    'status' => [
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'starting' => 'Départ',
        'stopping' => 'Arrêt',
        'crashed' => 'Crashé',
        'installing' => 'Installation',
        'restoring_backup' => 'Restauration de la sauvegarde',
        'install_failed' => 'Échec de l\'installation',
        'reinstall_failed' => 'Échec de la réinstallation',
        'suspended' => 'Suspendu',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Détails de base',
            'allocation' => 'Gestion des allocations',
            'feature_limits' => 'Limites des fonctionnalités de l\'application',
            'resources' => 'Gestion des ressources',
            'nest' => 'Configuration du nid',
            'docker' => 'Configuration du menu fixe',
            'startup' => 'Configuration de démarrage',
            'variables' => 'Variables de service',
        ],

        'fields' => [
            'name' => [
                'label' => 'Nom du serveur',
                'placeholder' => 'Nom du serveur',
                'helper' => 'Limites de caractères : a-z A-Z 0-9 _ - . et des espaces.',
            ],
            'owner' => [
                'label' => 'Propriétaire du serveur',
                'helper' => 'Adresse e-mail du propriétaire du serveur.',
            ],
            'description' => [
                'label' => 'Description du serveur',
                'helper' => 'Une brève description de ce serveur.',
            ],
            'start_on_completion' => [
                'label' => 'Démarrer le serveur une fois installé',
            ],
            'node' => [
                'label' => 'Nœud',
                'helper' => 'Le nœud sur lequel ce serveur sera déployé.',
            ],
            'allocation' => [
                'label' => 'Allocation par défaut',
                'helper' => 'L\'allocation principale qui sera attribuée à ce serveur.',
            ],
            'additional_allocations' => [
                'label' => 'Allocation(s) supplémentaire(s)',
                'helper' => 'Allocations supplémentaires à attribuer à ce serveur lors de la création.',
            ],
            'database_limit' => [
                'label' => 'Limite de base de données',
                'helper' => 'Nombre total de bases de données qu\'un utilisateur est autorisé à créer pour ce serveur.',
            ],
            'allocation_limit' => [
                'label' => 'Limite d\'allocation',
                'helper' => 'Nombre total d\'allocations qu\'un utilisateur est autorisé à créer pour ce serveur.',
            ],
            'backup_limit' => [
                'label' => 'Limite de sauvegarde',
                'helper' => 'Nombre total de sauvegardes pouvant être créées pour ce serveur.',
            ],
            'cpu' => [
                'label' => 'Limite du processeur',
                'helper' => 'Définissez 0 pour aucune limite de CPU. Un noyau virtuel complet, c\'est 100 %.',
            ],
            'threads' => [
                'label' => 'Épinglage du processeur',
                'helper' => 'Avancé : utilisez un seul nombre ou une liste séparée par des virgules, par exemple 0, 0-1,3 ou 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Mémoire',
                'helper' => 'Quantité maximale de mémoire autorisée pour ce conteneur. Réglez 0 pour illimité.',
            ],
            'swap' => [
                'label' => 'Échanger',
                'helper' => 'Définissez 0 pour désactiver le swap ou -1 pour autoriser un swap illimité.',
            ],
            'disk' => [
                'label' => 'Espace disque',
                'helper' => 'Définissez 0 pour autoriser une utilisation illimitée du disque.',
            ],
            'io' => [
                'label' => 'Poids du bloc IO',
                'helper' => 'Avancé : performances d\'E/S par rapport aux autres conteneurs en cours d\'exécution. La valeur doit être comprise entre 10 et 1 000.',
            ],
            'oom_disabled' => [
                'label' => 'Activer le tueur de MOO',
                'helper' => 'Termine le serveur s\'il dépasse les limites de mémoire.',
            ],
            'nest' => [
                'label' => 'Nid',
                'helper' => 'Sélectionnez le Nest sous lequel ce serveur sera regroupé.',
            ],
            'egg' => [
                'label' => 'Œuf',
                'helper' => 'Sélectionnez l\'œuf qui définira le fonctionnement de ce serveur.',
            ],
            'skip_scripts' => [
                'label' => 'Ignorer le script d\'installation d\'Œuf',
                'helper' => 'Si l\'œuf sélectionné est associé à un script d\'installation, le script s\'exécutera pendant l\'installation, à moins que cette case ne soit cochée.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Sélectionnez une image dans la liste déroulante ou saisissez une image personnalisée ci-dessous.',
            ],
            'custom_image' => [
                'label' => 'Image Docker personnalisée',
                'placeholder' => 'Ou entrez une image personnalisée...',
                'helper' => 'Il s\'agit de l\'image Docker par défaut qui sera utilisée pour exécuter ce serveur.',
            ],
            'startup' => [
                'label' => 'Commande de démarrage',
                'helper' => 'Substituts disponibles : {{SERVER_MEMORY}}, {{SERVER_IP}} et {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Sélectionnez un œuf pour configurer les variables de service',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Mode avancé',
            'helper' => 'Basculez pour afficher des options de configuration de serveur supplémentaires. Activez-la uniquement si vous comprenez les implications des paramètres supplémentaires.',
        ],
        'external_id' => [
            'label' => 'ID externe',
            'helper' => 'Identifiant unique facultatif pour ce serveur.',
        ],
        'owner' => [
            'label' => 'Propriétaire',
            'helper' => 'Sélectionnez l\'utilisateur propriétaire de ce serveur.',
        ],
        'name' => [
            'label' => 'Nom',
            'placeholder' => 'Nom du serveur',
            'helper' => 'Un nom court pour ce serveur.',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Descriptif du serveur',
            'helper' => 'Description facultative pour ce serveur.',
        ],
        'node' => [
            'label' => 'Nœud',
            'helper' => 'Le nœud sur lequel ce serveur sera déployé.',
        ],
        'allocation' => [
            'label' => 'Allocation primaire',
            'helper' => 'L\'allocation IP/port par défaut pour ce serveur.',
        ],
        'additional_allocations' => [
            'label' => 'Allocations supplémentaires',
            'helper' => 'Allocations supplémentaires facultatives à attribuer.',
        ],
        'nest' => [
            'label' => 'Nid',
            'helper' => 'Le nid de services pour ce serveur.',
        ],
        'egg' => [
            'label' => 'Œuf',
            'helper' => 'L\'œuf qui définit le comportement du serveur.',
        ],
        'startup' => [
            'label' => 'Commande de démarrage',
            'helper' => 'La commande de démarrage du serveur.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'helper' => 'Image Docker utilisée pour exécuter ce serveur.',
            'custom' => 'Coutume',
        ],
        'skip_scripts' => [
            'label' => 'Ignorer les scripts d\'œufs',
            'helper' => 'Ignorez les scripts d\'installation d\'Egg lors de la création.',
        ],
        'start_on_completion' => [
            'label' => 'Commencer à la fin',
            'helper' => 'Démarrez automatiquement le serveur après l\'installation.',
        ],
        'memory' => [
            'label' => 'Mémoire',
            'helper' => 'Allocation totale de mémoire. Réglez sur 0 pour illimité. (La mémoire illimitée ne fonctionne pas pour les œufs Minecraft en raison de la commande de démarrage)',
        ],
        'swap' => [
            'label' => 'Échanger',
            'helper' => 'Échangez l’allocation de mémoire. Réglez sur 0 pour désactiver le swap ou sur -1 pour autoriser un swap illimité.',
        ],
        'disk' => [
            'label' => 'Disque',
            'helper' => 'Allocation d\'espace disque. Réglez sur 0 pour illimité.',
        ],
        'io' => [
            'label' => 'Poids IO',
            'helper' => 'Priorité relative d’E/S du disque (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Limite du processeur en pourcentage. 100 % signifie un cœur complet, 200 % signifie deux cœurs complets, etc.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Entrez la taille en Gio',
            'helper' => 'Vous pouvez saisir des tailles en GiB en utilisant le suffixe "GiB" (par exemple 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Fils de processeur',
            'helper' => 'Épinglage de fil en option. Exemple : 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Désactiver le tueur de MOO',
            'helper' => 'Empêchez le noyau de tuer le processus en cas de manque de mémoire.',
        ],
        'database_limit' => [
            'label' => 'Limite de base de données',
            'helper' => 'Nombre maximum de bases de données.',
        ],
        'allocation_limit' => [
            'label' => 'Limite d\'allocation',
            'helper' => 'Nombre maximum d\'allocations.',
        ],
        'backup_limit' => [
            'label' => 'Limite de sauvegarde',
            'helper' => 'Nombre maximum de sauvegardes.',
        ],
        'environment' => [
            'key' => 'Variable',
            'value' => 'Valeur',
            'helper' => 'Variables d\'environnement pour cet œuf.',
        ],
        'use_custom_image' => [
            'label' => 'Utiliser une image personnalisée',
            'helper' => 'Basculez pour utiliser une image Docker personnalisée au lieu de celle fournie par l\'œuf.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Nom',
        'owner' => 'Propriétaire',
        'node' => 'Nœud',
        'allocation' => 'Allocation',
        'status' => 'Statut',
        'egg' => 'Œuf',
        'memory' => 'Mémoire',
        'disk' => 'Disque',
        'cpu' => 'CPU',
        'created' => 'Créé',
        'updated' => 'Mis à jour',
        'installed' => 'Installé',
        'no_status' => 'Aucun statut',
        'unlimited' => 'Illimité',
    ],

    'messages' => [
        'created' => 'Le serveur a été créé avec succès.',
        'updated' => 'Le serveur a été mis à jour avec succès.',
        'deleted' => 'Le serveur a été supprimé avec succès.',
    ],

    'actions' => [
        'edit' => 'Modifier',
        'random' => 'Aléatoire',
        'toggle_install_status' => 'Basculer l\'état de l\'installation',
        'suspend' => 'Suspendre',
        'unsuspend' => 'Annuler la suspension',
        'suspended' => 'Suspendu',
        'unsuspended' => 'Non suspendu',
        'reinstall' => 'Réinstaller',
        'delete' => 'Supprimer',
        'delete_forcibly' => 'Supprimer de force',
        'view' => 'Voir',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Vous essayez de supprimer l\'allocation par défaut pour ce serveur, mais il n\'y a pas d\'allocation de secours à utiliser.',
        'marked_as_failed' => 'Ce serveur a été marqué comme ayant échoué lors d\'une installation précédente. Le status actuel ne peut pas être modifié.',
        'bad_variable' => 'Une erreur de validation s\'est produite avec la variable :name.',
        'daemon_exception' => 'Une exception s\'est produite lors de la tentative de communication avec le daemon, entraînant un code de réponse HTTP/:code. Cette exception a été consignée. (ID de la requête : :request_id)',
        'default_allocation_not_found' => 'L\'allocation par défaut demandée n\'a pas été trouvée dans les allocations de ce serveur.',
    ],

    'alerts' => [
        'install_toggled' => 'Le statut d\'installation de ce serveur a été modifié.',
        'server_suspended' => 'Le serveur a été :action.',
        'server_reinstalled' => 'Ce serveur a été mis en file d\'attente pour une réinstallation qui commence dès maintenant.',
        'server_deleted' => 'Le serveur a été supprimé avec succès.',
        'server_delete_failed' => 'Échec de la suppression du serveur.',
        'startup_changed' => 'La configuration de démarrage de ce serveur a été mise à jour. Si le nid ou l\'oeuf de ce serveur a été modifié, une réinstallation va maintenant avoir lieu.',
        'server_created' => 'Le serveur a été créé avec succès sur le panneau. Veuillez patienter quelques minutes afin que le daemon puisse terminer l\'installation complète de ce serveur.',
        'build_updated' => 'Les détails de configuration de ce serveur ont été mis à jour. Certaines modifications peuvent nécessiter un redémarrage pour prendre effet.',
        'suspension_toggled' => 'Le statut de suspension du serveur a été modifié en :status.',
        'rebuild_on_boot' => 'Ce serveur a été marqué comme nécessitant une reconstruction du conteneur Docker. Cela se produira lors du prochain démarrage du serveur.',
        'details_updated' => 'Les détails du serveur ont été mis à jour avec succès.',
        'docker_image_updated' => 'Modification réussie de l\'image Docker par défaut à utiliser pour ce serveur. Un redémarrage est nécessaire pour appliquer cette modification.',
        'node_required' => 'Vous devez avoir au moins un noeud configuré avant de pouvoir ajouter un serveur au panel.',
        'transfer_nodes_required' => 'Vous devez avoir au moins deux noeuds configurés avant de pouvoir transférer des serveurs.',
        'transfer_started' => 'Le transfert du serveur a commencé.',
        'transfer_not_viable' => 'Le noeud que vous avez sélectionné ne dispose pas de l\'espace disque ou de la mémoire requis pour accueillir ce serveur.',
        'primary_allocation_updated' => 'Allocation primaire mise à jour.',
        'database_created' => 'Base de données créée.',
        'database_password_reset' => 'Réinitialisation du mot de passe de la base de données.',
        'database_deleted' => 'Base de données supprimée.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Configuration de construction',
            'startup' => 'Démarrer',
            'manage' => 'Gérer',
        ],

        'sections' => [
            'resource_management' => 'Gestion des ressources',
            'application_feature_limits' => 'Limites des fonctionnalités de l\'application',
            'allocation_management' => 'Gestion des allocations',
            'startup_command_modification' => 'Modification de la commande de démarrage',
            'service_configuration' => 'Configuration des services',
            'docker_image_configuration' => 'Configuration des images Docker',
            'service_variables' => 'Variables de service',
            'reinstall_server' => 'Réinstaller le serveur',
            'install_status' => 'Statut d\'installation',
            'suspend_server' => 'Suspendre le serveur',
            'unsuspend_server' => 'Annuler la suspension du serveur',
            'transfer_server' => 'Serveur de transfert',
            'delete_server' => 'Supprimer le serveur',
        ],

        'section_descriptions' => [
            'service_configuration' => 'La modification de ces valeurs peut déclencher une réinstallation. Le serveur sera immédiatement arrêté pour cette opération.',
            'reinstall_server' => 'Cela réinstallera le serveur avec les scripts de service attribués. Cela pourrait écraser les données du serveur.',
            'install_status' => 'Changez l’état de l’installation de désinstallé à installé, ou vice versa.',
            'suspend_server' => 'Cela arrêtera les processus en cours d\'exécution et empêchera l\'utilisateur de gérer le serveur via le panneau ou l\'API.',
            'unsuspend_server' => 'Cela réactivera la suspension du serveur et restaurera l\'accès utilisateur normal.',
            'transfer_server_transferring' => 'Ce serveur est actuellement en cours de transfert vers un autre nœud.',
            'transfer_server' => 'Transférez ce serveur vers un autre nœud connecté à ce panneau.',
            'delete_server' => 'Cela supprime définitivement le serveur du panneau et de l\'agent. La suppression forcée ignore la suppression de l\'agent si nécessaire.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Nom du serveur',
                'helper' => 'Limites de caractères : a-zA-Z0-9_-, espaces et caractères imprimables standard.',
            ],
            'server_owner' => [
                'label' => 'Propriétaire du serveur',
                'helper' => 'Le changement de propriétaire révoque automatiquement les jetons de démon du propriétaire précédent.',
            ],
            'server_description' => [
                'label' => 'Description du serveur',
                'helper' => 'Une brève description de ce serveur.',
            ],
            'server_uuid' => [
                'label' => 'UUID du serveur',
            ],
            'server_uuid_short' => [
                'label' => 'UUID du serveur (court)',
            ],
            'external_identifier' => [
                'label' => 'Identifiant externe',
                'helper' => 'Laissez vide pour ne pas attribuer d’identifiant externe. L\'ID externe doit être unique pour ce serveur.',
            ],
            'game_port' => [
                'label' => 'Port de jeu',
                'helper' => 'L\'adresse de connexion par défaut qui sera utilisée pour ce serveur de jeu.',
            ],
            'additional_ports' => [
                'label' => 'Ports supplémentaires',
                'helper' => 'Attribuez ou supprimez des ports supplémentaires. Des ports identiques sur différentes IP ne peuvent pas être attribués au même serveur.',
            ],
            'startup_command' => [
                'label' => 'Commande de démarrage',
                'helper' => 'Disponible par défaut : {{SERVER_MEMORY}}, {{SERVER_IP}} et {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Commande de démarrage par défaut',
                'error' => 'ERREUR : Démarrage non défini !',
            ],
            'cpu_limit' => [
                'label' => 'Limite du processeur',
                'helper' => 'Chaque noyau virtuel est à 100 %. Définissez 0 pour un temps CPU illimité.',
            ],
            'cpu_pinning' => [
                'label' => 'Épinglage du processeur',
                'helper' => 'Avancé : laissez vide pour tous les cœurs. Exemples : 0, 0-1,3 ou 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Mémoire allouée',
                'helper' => 'Quantité maximale de mémoire autorisée pour ce conteneur. Réglez 0 pour illimité.',
            ],
            'allocated_swap' => [
                'label' => 'Échange alloué',
                'helper' => 'Définissez 0 pour désactiver le swap ou -1 pour autoriser un swap illimité.',
            ],
            'disk_space_limit' => [
                'label' => 'Limite d\'espace disque',
                'helper' => 'Définissez 0 pour autoriser une utilisation illimitée du disque.',
            ],
            'block_io_proportion' => [
                'label' => 'Proportion d\'E/S de bloc',
                'helper' => 'Avancé : performances d\'E/S par rapport aux autres conteneurs en cours d\'exécution. La valeur doit être comprise entre 10 et 1 000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Désactiver le tueur de MOO',
                'helper' => 'L\'activation de MOO Killer peut entraîner la fermeture inattendue des processus du serveur.',
            ],
            'database_limit' => [
                'label' => 'Limite de base de données',
                'helper' => 'Nombre total de bases de données qu\'un utilisateur est autorisé à créer pour ce serveur.',
            ],
            'allocation_limit' => [
                'label' => 'Limite d\'allocation',
                'helper' => 'Nombre total d\'allocations qu\'un utilisateur est autorisé à créer pour ce serveur.',
            ],
            'backup_limit' => [
                'label' => 'Limite de sauvegarde',
                'helper' => 'Nombre total de sauvegardes pouvant être créées pour ce serveur.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Sélectionnez une image dans la liste déroulante ou saisissez une image personnalisée ci-dessous.',
            ],
            'custom_image' => [
                'label' => 'Image personnalisée',
                'placeholder' => 'Ou entrez une image personnalisée...',
                'helper' => 'Il s\'agit de l\'image Docker qui sera utilisée pour exécuter ce serveur.',
            ],
            'transfer_node' => [
                'label' => 'Nœud',
                'helper' => 'Le nœud vers lequel ce serveur sera transféré.',
            ],
            'transfer_allocation' => [
                'label' => 'Allocation par défaut',
                'helper' => 'L\'allocation principale qui sera attribuée à ce serveur.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Allocation(s) supplémentaire(s)',
                'helper' => 'Allocations supplémentaires à attribuer à ce serveur lors du transfert.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Réinstaller le serveur',
            'toggle_install_status' => 'Basculer l\'état de l\'installation',
            'suspend_server' => 'Suspendre le serveur',
            'unsuspend_server' => 'Annuler la suspension du serveur',
            'transfer_server' => 'Serveur de transfert',
            'confirm' => 'Confirmer',
            'delete_server' => 'Supprimer le serveur',
            'forcibly_delete_server' => 'Supprimer de force le serveur',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primaire',
            'notes' => 'Remarques',
            'created' => 'Créé',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Aucun alias attribué',
        ],

        'actions' => [
            'make_primary' => 'Rendre primaire',
        ],
    ],

    'databases' => [
        'title' => 'Bases de données',

        'table' => [
            'database' => 'Base de données',
            'username' => 'Nom d\'utilisateur',
            'remote' => 'Télécommande',
            'host' => 'Hôte',
            'max_connections' => 'Connexions maximales',
            'created' => 'Créé',
        ],

        'placeholder' => [
            'unlimited' => 'Illimité',
        ],

        'actions' => [
            'create_database' => 'Créer une base de données',
            'reset_password' => 'Réinitialiser le mot de passe',
            'delete' => 'Supprimer',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Nom de la base de données',
                'helper' => 'Le panneau le préfixera avec l\'ID du serveur, correspondant à l\'ancien panneau d\'administration.',
            ],
            'database_host' => [
                'label' => 'Hôte de base de données',
            ],
            'remote' => [
                'label' => 'Télécommande',
            ],
            'max_connections' => [
                'label' => 'Connexions maximales',
            ],
        ],
    ],
];
