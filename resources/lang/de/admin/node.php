<?php

return [
    'label' => 'Node',
    'plural-label' => 'Nodes',

    'sections' => [
        'overview' => [
            'title' => 'Übersicht',
            'information-label' => 'Node-Informationen',
            'version-label' => 'Agent-Version',
            'architecture-label' => 'Architektur',
            'kernel-label' => 'Kernel',
            'cpus-label' => 'CPU-Threads',
            'cpu-usage-label' => 'CPU-Auslastung',
            'memory-usage-label' => 'Speicherauslastung',
            'disk-usage-label' => 'Festplattenauslastung',
        ],
        'tabs' => [
            'title' => 'Node-Konfiguration',
        ],
        'identity' => [
            'title' => 'Identität',
            'description' => 'Grundlegende Node-Informationen.',
        ],
        'connection' => [
            'title' => 'Verbindungsdetails',
            'description' => 'Konfiguriere, wie eine Verbindung zu dieser Node hergestellt wird.',
        ],
        'resources' => [
            'title' => 'Ressourcenzuweisung',
            'description' => 'Definiere Speicher- und Festplattenlimits für diese Node.',
        ],
        'daemon' => [
            'title' => 'Daemon-Konfiguration',
            'description' => 'Konfiguriere daemon-spezifische Einstellungen.',
        ],
        'configuration' => [
            'title' => 'Konfiguration',
            'config_description' => 'Konfigurationsdatei',
            'deploy_description' => 'Generiere einen benutzerdefinierten Deployment-Befehl zur Konfiguration des Agents auf dem Zielserver.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Öffentlich',
            'helper' => 'Wenn eine Node privat gesetzt wird, wird die automatische Bereitstellung auf dieser Node verhindert.',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Node-Name',
            'helper' => 'Ein beschreibender Name für diese Node.',
        ],
        'description' => [
            'label' => 'Beschreibung',
            'placeholder' => 'Node-Beschreibung',
            'helper' => 'Optionale Beschreibung für diese Node.',
        ],
        'location' => [
            'label' => 'Standort',
            'helper' => 'Der Standort, dem diese Node zugewiesen ist.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'Vollqualifizierter Domainname oder IP-Adresse.',
        ],
        'ssl' => [
            'label' => 'Verwendet SSL',
            'helper' => 'Ob der Daemon auf dieser Node SSL für sichere Kommunikation verwendet.',
            'helper_forced' => 'Dieses Panel läuft über HTTPS, daher ist SSL für diese Node erforderlich.',
        ],
        'behind_proxy' => [
            'label' => 'Hinter Proxy',
            'helper' => 'Aktivieren, wenn sich diese Node hinter einem Proxy wie Cloudflare befindet.',
        ],
        'maintenance_mode' => [
            'label' => 'Wartungsmodus',
            'helper' => 'Verhindert die Erstellung neuer Server auf dieser Node.',
        ],
        'memory' => [
            'label' => 'Gesamtspeicher',
            'helper' => 'Gesamter verfügbarer Speicher auf dieser Node in MiB.',
        ],
        'memory_overallocate' => [
            'label' => 'Speicherüberbelegung',
            'helper' => 'Prozentsatz des Speichers, der überbelegt werden darf. Verwende -1, um die Überprüfung zu deaktivieren.',
        ],
        'disk' => [
            'label' => 'Gesamter Festplattenspeicher',
            'helper' => 'Gesamter verfügbarer Festplattenspeicher auf dieser Node in MiB.',
        ],
        'disk_overallocate' => [
            'label' => 'Festplattenüberbelegung',
            'helper' => 'Prozentsatz des Festplattenspeichers, der überbelegt werden darf. Verwende -1, um die Überprüfung zu deaktivieren.',
        ],
        'upload_size' => [
            'label' => 'Maximale Upload-Größe',
            'helper' => 'Maximale Dateigröße für Uploads über das Web-Panel.',
        ],
        'daemon_base' => [
            'label' => 'Basisverzeichnis',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Verzeichnis, in dem Serverdateien gespeichert werden.',
        ],
        'daemon_listen' => [
            'label' => 'Daemon-Port',
            'helper' => 'Port, auf dem der Daemon HTTP-Kommunikation akzeptiert.',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP-Port',
            'helper' => 'Port für SFTP-Verbindungen.',
        ],
        'daemon_token_id' => [
            'label' => 'Token-ID',
        ],
        'container_text' => [
            'label' => 'Container-Präfix',
            'helper' => 'Textpräfix, das in Containernamen angezeigt wird.',
        ],
    ],

    'table' => [
        'health' => 'Status',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'Browser-Konsole prüfen',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Name',
        'location' => 'Standort',
        'fqdn' => 'FQDN',
        'scheme' => 'Protokoll',
        'public' => 'Öffentlich',
        'behind_proxy' => 'Hinter Proxy',
        'maintenance_mode' => 'Wartungsmodus',
        'memory' => 'Speicher',
        'memory_overallocate' => 'Speicherüberbelegung',
        'disk' => 'Festplatte',
        'disk_overallocate' => 'Festplattenüberbelegung',
        'upload_size' => 'Maximale Upload-Größe',
        'daemon_listen' => 'Daemon-Port',
        'daemon_sftp' => 'SFTP-Port',
        'daemon_base' => 'Basisverzeichnis',
        'servers' => 'Server',
        'created' => 'Erstellt',
        'updated' => 'Aktualisiert',
    ],

    'filters' => [
        'public' => 'Öffentlich',
        'maintenance' => 'Wartung',
        'public_true' => 'Öffentlich',
        'public_false' => 'Privat',
        'maintenance_true' => 'In Wartung',
        'maintenance_false' => 'Aktiv',
    ],

    'actions' => [
        'create' => 'Erstellen',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
        'view' => 'Ansehen',
        'random' => 'Zufällig',
        'view_monitoring' => 'Monitoring anzeigen',
    ],

    'deployment' => [
        'generate_label' => 'Deployment-Token generieren',
        'modal_heading' => 'Auto-Deploy-Befehl',
        'modal_description' => 'Führe diesen Befehl auf deiner Node aus, um den Agent automatisch zu konfigurieren.',
        'modal_close' => 'Schließen',
        'command_label' => 'Deployment-Befehl',
        'command_helper' => 'Kopiere und führe diesen Befehl auf deinem Node-Server aus.',
        'token_success' => 'Token erfolgreich generiert',
        'token_success_body' => 'Kopiere und führe den folgenden Befehl auf deiner Node aus.',
        'save_first' => 'Bitte speichere zuerst die Node.',
        'auto_generated_key' => 'Automatisch generierter Node-Deployment-Schlüssel.',
        'error' => 'Fehler beim Generieren des Tokens. Bitte versuche es erneut.',
    ],

    'general' => [
        'na' => 'N/V',
        'unavailable' => 'Nicht verfügbar',
    ],

    'messages' => [
        'created' => 'Node wurde erfolgreich erstellt.',
        'updated' => 'Node wurde erfolgreich aktualisiert.',
        'deleted' => 'Node wurde erfolgreich gelöscht.',
        'cannot_delete_with_servers' => 'Eine Node mit aktiven Servern kann nicht gelöscht werden.',
    ],

    'allocations' => [
        'label' => 'Allocations',
        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'server' => 'Server',
            'notes' => 'Notizen',
            'created' => 'Erstellt',
            'unassigned' => 'Nicht zugewiesen',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP-Adresse',
                'helper' => 'Unterstützt einzelne IPs oder CIDR (z.B. 192.0.2.1 oder 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Ports',
                'helper' => 'Gib Ports oder Bereiche ein (z.B. 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP-Alias',
                'helper' => 'Optionaler Alias, der anstelle der IP angezeigt wird.',
            ],
        ],
        'actions' => [
            'add' => 'Allocation hinzufügen',
            'delete' => 'Löschen',
        ],
        'messages' => [
            'created' => 'Allocation hinzugefügt.',
            'deleted' => 'Allocation gelöscht.',
            'failed' => 'Allocation-Aktion fehlgeschlagen.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'Der angegebene FQDN oder die IP-Adresse konnte nicht zu einer gültigen IP-Adresse aufgelöst werden.',
        'fqdn_required_for_ssl' => 'Ein vollqualifizierter Domainname, der zu einer öffentlichen IP-Adresse aufgelöst wird, ist erforderlich, um SSL auf dieser Node zu verwenden.',
    ],
    'notices' => [
        'allocations_added' => 'Allocations wurden dieser Node erfolgreich hinzugefügt.',
        'node_deleted' => 'Node wurde erfolgreich aus dem Panel entfernt.',
        'location_required' => 'Du musst mindestens einen Standort konfiguriert haben, bevor du eine Node zu diesem Panel hinzufügen kannst.',
        'node_created' => 'Neue Node erfolgreich erstellt. Du kannst den Daemon auf dieser Maschine automatisch konfigurieren, indem du den Tab „Konfiguration“ besuchst. Bevor du Server hinzufügen kannst, musst du mindestens eine Allocation erstellen.',
        'node_updated' => 'Die Node-Informationen wurden aktualisiert. Falls Daemon-Einstellungen geändert wurden, musst du den Daemon neu starten, damit die Änderungen wirksam werden.',
        'unallocated_deleted' => 'Alle nicht zugewiesenen Ports für <code>:ip</code> wurden gelöscht.',
    ],
];
