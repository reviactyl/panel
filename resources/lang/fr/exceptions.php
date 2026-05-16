<?php

return [
    'daemon_connection_failed' => 'Une exception s\'est produite lors de la tentative de communication avec le daemon, résultant en un code de réponse HTTP/:code. Cette exception a été enregistrée.',
    'node' => [
        'servers_attached' => 'Un noeud ne doit avoir aucun serveur lié pour pouvoir être supprimé.',
        'daemon_off_config_updated' => 'La configuration du démon a été mise à jour, mais une erreur s\'est produite lors de la tentative de mise à jour automatique du fichier de configuration sur le démon. Vous devrez mettre à jour manuellement le fichier de configuration (config.yml) pour que le démon applique ces modifications.',
    ],
    'allocations' => [
        'server_using' => 'Un serveur est actuellement assigné à cette allocation. Une allocation ne peut être supprimée que si aucun serveur n\'y est actuellement assigné.',
        'too_many_ports' => 'L\'ajout de plus de 1000 ports dans une seule plage à la fois n\'est pas pris en charge.',
        'invalid_mapping' => 'Le mappage fourni pour :port était invalide et n\'a pas pu être traité.',
        'cidr_out_of_range' => 'La notation CIDR n\'autorise que les masques entre /25 et /32.',
        'port_out_of_range' => 'Les ports dans une allocation doivent être supérieurs à 1024 et inférieurs ou égaux à 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Un Nest avec des serveurs actifs qui y sont attachés ne peut pas être supprimé du Panel.',
        'egg' => [
            'delete_has_servers' => 'Un Egg avec des serveurs actifs qui y sont attachés ne peut pas être supprimé du Panel.',
            'invalid_copy_id' => 'L\'Egg sélectionné pour copier un script n\'existe pas ou copie lui-même un script.',
            'must_be_child' => 'La directive "Copier les paramètres depuis" pour cet Egg doit être une option enfant pour le Nest sélectionné.',
            'has_children' => 'Cet Egg est un parent d\'un ou plusieurs autres Eggs. Veuillez supprimer ces Eggs avant de supprimer cet Egg.',
        ],
        'variables' => [
            'env_not_unique' => 'La variable d\'environnement :name doit être unique pour cet Egg.',
            'reserved_name' => 'La variable d\'environnement :name est protégée et ne peut pas être assignée à une variable.',
            'bad_validation_rule' => 'La règle de validation ":rule" n\'est pas une règle valide pour cette application.',
        ],
        'importer' => [
            'json_error' => 'Une erreur s\'est produite lors de la tentative d\'analyse du fichier JSON : :error.',
            'file_error' => 'Le fichier JSON fourni n\'était pas valide.',
            'invalid_json_provided' => 'Le fichier JSON fourni n\'est pas dans un format reconnaissable.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'La modification de votre propre compte de sous-utilisateur n\'est pas autorisée.',
        'user_is_owner' => 'Vous ne pouvez pas ajouter le propriétaire du serveur en tant que sous-utilisateur pour ce serveur.',
        'subuser_exists' => 'Un utilisateur avec cette adresse e-mail est déjà assigné en tant que sous-utilisateur pour ce serveur.',
    ],
    'databases' => [
        'delete_has_databases' => 'Impossible de supprimer un serveur hôte de base de données qui a des bases de données actives liées.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'L\'intervalle maximum pour une tâche chaînée est de 15 minutes.',
    ],
    'locations' => [
        'has_nodes' => 'Impossible de supprimer un emplacement qui a des noeuds actifs attachés.',
    ],
    'users' => [
        'node_revocation_failed' => 'Échec de la révocation des clés sur <a href=":link">Node #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Aucun noeud satisfaisant aux exigences spécifiées pour le déploiement automatique n\'a été trouvé.',
        'no_viable_allocations' => 'Aucune allocation satisfaisant aux exigences pour le déploiement automatique n\'a été trouvée.',
    ],
    'api' => [
        'resource_not_found' => 'La ressource demandée n\'existe pas sur ce serveur.',
    ],
    'social' => [
        'unlink_only_login' => 'Vous ne pouvez pas dissocier votre seule méthode de connexion sans définir au préalable un mot de passe.',
    ],
];
