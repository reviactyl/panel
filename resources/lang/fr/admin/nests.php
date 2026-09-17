<?php

return [

    'label' => 'Nids',
    'plural_label' => 'Nids',

    'sections' => [
        'configuration' => 'Configuration du nid',
    ],

    'fields' => [
        'name' => 'Nom',
        'author' => 'Auteur',
        'description' => 'Description',
    ],

    'helpers' => [
        'name' => 'Un nom unique utilisé pour identifier ce nid.',
        'author' => 'L’auteur de ce nid. Doit avoir une adresse e-mail valide.',
        'description' => 'Une description de ce nid.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nom',
        'author' => 'Auteur',
        'eggs' => 'Œufs',
        'servers' => 'Serveurs',
    ],

    'actions' => [
        'import' => 'Importer des œufs',
    ],

    'import' => [
        'file_label' => 'Fichier œuf (JSON)',
        'nest_label' => 'Nid associé',
        'file_not_found' => 'Fichier introuvable',
        'file_not_found_body' => 'Impossible de localiser le fichier téléchargé.',
        'invalid_format' => 'Format de fichier invalide',
        'invalid_format_body' => 'Format de fichier inattendu reçu.',
        'success' => 'Oeuf importé avec succès',
        'failed' => 'Échec de l\'importation de l\'œuf',
    ],

    'notices' => [
        'created' => 'Un nouveau nid, :name, a été créer.',
        'deleted' => 'Suppression réussie du nid demandé dans le panel.',
        'updated' => 'Modification réussie du nid demandé dans le panel.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Importation réussie de cet oeuf et des variables associées.',
            'updated_via_import' => 'Cet oeuf a été mis à jour à l\'aide du fichier fourni.',
            'deleted' => 'L\'oeuf demandé a été supprimé du panel.',
            'updated' => 'La configuration de l\'oeuf a été mise à jour avec succès.',
            'script_updated' => 'Le script d\'installation de l\'oeuf a été mis à jour et s\'exécutera à chaque installation des serveurs.',
            'egg_created' => 'Un nouvel oeuf a été posé avec succès. Vous devrez redémarrer tous les démons en cours d\'exécution pour appliquer ce nouvel œuf.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'La variable ":variable" a été supprimée et ne sera plus disponible sur les serveurs une fois réinstaller.',
            'variable_updated' => 'La variable ":variable" a été modifiée. Vous devrez reconstruire tous les serveurs utilisant cette variable afin d\'appliquer les modifications.',
            'variable_created' => 'La nouvelle variable a été créée et attribuée à cet oeuf avec succès.',
        ],
    ],
];
