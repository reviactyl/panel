<?php

return [
    'daemon_connection_failed' => 'Es trat ein Fehler bei der Kommunikation mit dem Daemon auf, der zu einem HTTP/:code Antwortcode führte. Dieser Fehler wurde protokolliert.',
    'node' => [
        'servers_attached' => 'Ein Node darf keine verbundenen Server haben, um gelöscht werden zu können.',
        'daemon_off_config_updated' => 'Die Daemon-Konfiguration <strong>wurde aktualisiert</strong>, jedoch trat ein Fehler beim Versuch auf, die Konfigurationsdatei auf dem Daemon automatisch zu aktualisieren. Sie müssen die Konfigurationsdatei (config.yml) für den Daemon manuell aktualisieren, um diese Änderungen zu übernehmen.',
    ],
    'allocations' => [
        'server_using' => 'Dieser Zuweisung ist derzeit ein Server zugeordnet. Eine Zuweisung kann nur gelöscht werden, wenn derzeit kein Server zugewiesen ist.',
        'too_many_ports' => 'Das Hinzufügen von mehr als 1000 Ports in einem einzigen Bereich auf einmal wird nicht unterstützt.',
        'invalid_mapping' => 'Die für :port angegebene Zuordnung war ungültig und konnte nicht verarbeitet werden.',
        'cidr_out_of_range' => 'Die CIDR-Notation erlaubt nur Masken zwischen /25 und /32.',
        'port_out_of_range' => 'Ports in einer Zuweisung müssen größer als 1024 und kleiner oder gleich 65535 sein.',
    ],
    'nest' => [
        'delete_has_servers' => 'Ein Nest mit aktiven Servern kann nicht über das Panel gelöscht werden.',
        'egg' => [
            'delete_has_servers' => 'Ein Ei mit aktiven Servern kann nicht über das Panel gelöscht werden.',
            'invalid_copy_id' => 'Das zum Kopieren eines Skripts ausgewählte Ei existiert entweder nicht oder kopiert selbst ein Skript.',
            'must_be_child' => 'Die Anweisung "Einstellungen kopieren von" für dieses Ei muss eine untergeordnete Option für das ausgewählte Nest sein.',
            'has_children' => 'Dieses Ei ist ein Elternteil für ein oder mehrere andere Eier. Bitte löschen Sie diese Eier, bevor Sie dieses Ei löschen.',
        ],
        'variables' => [
            'env_not_unique' => 'Die Umgebungsvariable :name muss für dieses Ei eindeutig sein.',
            'reserved_name' => 'Die Umgebungsvariable :name ist geschützt und kann keiner Variable zugewiesen werden.',
            'bad_validation_rule' => 'Die Validierungsregel ":rule" ist keine gültige Regel für diese Anwendung.',
        ],
        'importer' => [
            'json_error' => 'Beim Versuch, die JSON-Datei zu parsen, ist ein Fehler aufgetreten: :error.',
            'file_error' => 'Die bereitgestellte JSON-Datei war ungültig.',
            'invalid_json_provided' => 'Die bereitgestellte JSON-Datei hat kein erkennbares Format.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Das Bearbeiten des eigenen Subuser-Kontos ist nicht gestattet.',
        'user_is_owner' => 'Sie können den Serverbesitzer nicht als Subuser für diesen Server hinzufügen.',
        'subuser_exists' => 'Ein Benutzer mit dieser E-Mail-Adresse ist bereits als Subuser für diesen Server zugewiesen.',
    ],
    'databases' => [
        'delete_has_databases' => 'Ein Datenbank-Hostserver, mit dem aktive Datenbanken verknüpft sind, kann nicht gelöscht werden.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'Die maximale Intervallzeit für eine verkettete Aufgabe beträgt 15 Minuten.',
    ],
    'locations' => [
        'has_nodes' => 'Ein Standort, an den aktive Nodes angeschlossen sind, kann nicht gelöscht werden.',
    ],
    'users' => [
        'node_revocation_failed' => 'Schlüssel auf <a href=":link">Node #:node</a> konnten nicht widerrufen werden. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Es konnten keine Nodes gefunden werden, die die für die automatische Bereitstellung angegebenen Anforderungen erfüllen.',
        'no_viable_allocations' => 'Es wurden keine Zuweisungen gefunden, die die Anforderungen für die automatische Bereitstellung erfüllen.',
    ],
    'api' => [
        'resource_not_found' => 'Die angeforderte Ressource existiert auf diesem Server nicht.',
    ],
];