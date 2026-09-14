<?php

return [
    'daemon_connection_failed' => 'Es trat ein Fehler bei der Kommunikation mit dem Daemon auf, der zu einem HTTP/:code Antwortcode führte. Dieser Fehler wurde protokolliert.',
    'node' => [
        'servers_attached' => 'Ein Node darf keine verbundenen Server haben, um gelöscht werden zu können.',
        'daemon_off_config_updated' => 'Die Daemonkonfiguration wurde aktualisiert, aber ein Fehler ist aufgetreten während versucht wurde, die Konfigurationsdatei beim Daemon anzupassen. Sie werden die Konfigurationsdatei (config.yml) manuell bearbeiten müssen, um diese Änderungen zu übernehmen.',
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
    'subuser_preview' => [
        'start_blocked' => 'Solange der Vorschaumodus aktiv ist, können Sie keine weitere Vorschau starten.',
        'owner_only' => 'Nur der Serverbesitzer kann eine Vorschau eines Unterbenutzers anzeigen.',
        'session_unavailable' => 'Diese Vorschau ist nicht mehr verfügbar.',
        'session_expired' => 'Die Gültigkeitsdauer dieser Vorschau ist abgelaufen.',
        'concurrent_start' => 'Eine Vorschau-Sitzung wird bereits gestartet.',
        'account_unavailable' => 'Während der Vorschau für Unterbenutzer sind keine Kontoinformationen verfügbar.',
        'categories_unavailable' => 'Kategorien für persönliche Server sind während der Vorschau für Unterbenutzer nicht verfügbar.',
        'permission_denied' => 'Sie haben keine Berechtigung, diese Aktion in der Vorschau auszuführen.',
        'resource_unavailable' => 'Diese Ressource ist während der Vorschau für Unterbenutzer nicht verfügbar.',
        'live_connection_unavailable' => 'Diese Live-Verbindung ist während der Vorschau für Unterbenutzer nicht verfügbar.',
        'file_not_found' => 'Die angeforderte Datei ist in dieser Vorschau nicht vorhanden.',
        'file_too_large' => 'Diese Datei ist zu groß, um sie in der Vorschau anzuzeigen.',
        'state_too_large' => 'Diese Vorschau hat ihr Speicherlimit erreicht.',
        'unsafe_pull_url' => 'In die Vorschau können nur sichere HTTPS-URLs geladen werden.',
        'action_unavailable' => 'Diese Aktion ist während der Vorschau für Unterbenutzer nicht verfügbar.',
        'database_limit' => 'Dieser Server hat sein Datenbanklimit erreicht.',
        'database_host_unavailable' => 'Für diesen Server ist kein Datenbank-Host verfügbar.',
        'task_limit' => 'Dieser Zeitplan hat sein Aufgabenlimit erreicht.',
        'allocation_limit' => 'Dieser Server hat sein Zuteilungslimit erreicht.',
        'allocation_unavailable' => 'Für diesen Server stehen keine zusätzlichen Ressourcen zur Verfügung.',
        'primary_allocation' => 'Die primäre Zuweisung kann nicht entfernt werden.',
        'backup_limit' => 'Dieser Server hat sein Backup-Limit erreicht.',
        'locked_backup' => 'Ein gesperrtes Backup kann nicht gelöscht werden.',
        'variable_unavailable' => 'Die Umgebungsvariable ist nicht verfügbar oder schreibgeschützt.',
        'docker_image_unavailable' => 'Das ausgewählte Docker-Image ist für diesen Server nicht verfügbar.',
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
    'social' => [
        'unlink_only_login' => 'Du kannst deine einzige Login-Möglichkeit nicht entfernen, ohne zuerst ein Passwort zu setzen.',
    ],
];
