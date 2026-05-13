<?php

return [
    'label' => 'Knoten',
    'plural-label' => 'Knoten',

    'sections' => [
        'overview' => [
            'title' => 'Überblick',
            'information-label' => 'Knoteninformationen',
            'version-label' => 'Agent-Version',
            'architecture-label' => 'Architektur',
            'kernel-label' => 'Kernel',
            'cpus-label' => 'CPU-Threads',
            'cpu-usage-label' => 'CPU-Auslastung',
            'memory-usage-label' => 'Speichernutzung',
            'disk-usage-label' => 'Festplattennutzung',
        ],
        'tabs' => [
            'title' => 'Knotenkonfiguration',
        ],
        'identity' => [
            'title' => 'Identität',
            'description' => 'Grundlegende Node Informationen.',
        ],
        'connection' => [
            'title' => 'Verbindungsdetails',
            'description' => 'Konfigurieren Sie, wie eine Verbindung zu dieser Node hergestellt wird.',
        ],
        'resources' => [
            'title' => 'Ressourcenzuweisung',
            'description' => 'Definieren Sie Speicher- und Festplattenlimits für diese Node.',
        ],
        'daemon' => [
            'title' => 'Daemon-Konfiguration',
            'description' => 'Konfigurieren Sie daemon-spezifische Einstellungen.',
        ],
        'configuration' => [
            'title' => 'Konfiguration',
            'config_description' => 'Konfigurationsdatei',
            'deploy_description' => 'Generieren Sie einen benutzerdefinierten Bereitstellungsbefehl, der zum Konfigurieren des Agenten auf dem Zielserver verwendet werden kann.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Öffentlich',
            'helper' => 'Wenn Sie eine Node auf privat setzen, wird die automatische Bereitstellung auf dieser Node verweigert.',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Knotenname',
            'helper' => 'Ein beschreibender Name für diese Node.',
        ],
        'description' => [
            'label' => 'Beschreibung',
            'placeholder' => 'Node Beschreibung',
            'helper' => 'Optionale Beschreibung für diese Node.',
        ],
        'location' => [
            'label' => 'Standort',
            'helper' => 'Der Standort, dem diese Node zugewiesen ist.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'Vollständig qualifizierter Domainname oder IP-Adresse.',
        ],
        'ssl' => [
            'label' => 'Verwendet SSL',
            'helper' => 'Ob der Daemon auf dieser Node so konfiguriert ist, dass er SSL für die sichere Kommunikation verwendet.',
            'helper_forced' => 'Dieses Panel läuft über HTTPS, daher ist SSL für diese Node erzwungen.',
        ],
        'behind_proxy' => [
            'label' => 'Hinter Proxy',
            'helper' => 'Aktivieren, wenn sich diese Node hinter einem Proxy wie Cloudflare befindet.',
        ],
        'maintenance_mode' => [
            'label' => 'Wartungsmodus',
            'helper' => 'Verhindert, dass neue Server auf dieser Node erstellt werden.',
        ],
        'memory' => [
            'label' => 'Gesamtspeicher',
            'helper' => 'Gesamtspeicher in MiB, der auf dieser Node verfügbar ist.',
        ],
        'memory_overallocate' => [
            'label' => 'Speicherüberbelegung',
            'helper' => 'Prozentsatz des Speichers, der überbelegt werden soll. Verwenden Sie -1, um die Überprüfung zu deaktivieren.',
        ],
        'disk' => [
            'label' => 'Gesamter Festplattenspeicher',
            'helper' => 'Gesamter Festplattenspeicher in MiB, der auf dieser Node verfügbar ist.',
        ],
        'disk_overallocate' => [
            'label' => 'Festplattenüberbelegung',
            'helper' => 'Prozentsatz der Festplatte, der überbelegt werden soll. Verwenden Sie -1, um die Überprüfung zu deaktivieren.',
        ],
        'upload_size' => [
            'label' => 'Maximale Upload-Größe',
            'helper' => 'Maximale Dateiupload-Größe, die über das Web-Panel erlaubt ist.',
        ],
        'daemon_base' => [
            'label' => 'Basisverzeichnis',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Verzeichnis, in dem die Serverdateien gespeichert werden.',
        ],
        'daemon_listen' => [
            'label' => 'Daemon-Port',
            'helper' => 'Der Port, auf dem der Daemon für die HTTP-Kommunikation lauscht.',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP-Port',
            'helper' => 'Der Port, der für SFTP-Verbindungen verwendet wird.',
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
        'health' => 'Gesundheit',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'Überprüfen Sie die Browserkonsole',
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
        'maintenance_true' => 'Unter Wartung',
        'maintenance_false' => 'Aktiv',
    ],

    'actions' => [
        'create' => 'Erstellen',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
        'view' => 'Ansehen',
        'random' => 'Zufällig',
        'view_monitoring' => 'Überwachung anzeigen',
    ],

    'deployment' => [
        'generate_label' => 'Bereitstellungstoken generieren',
        'modal_heading' => 'Befehl zur automatischen Bereitstellung',
        'modal_description' => 'Führen Sie diesen Befehl auf Ihrem Knoten aus, um den Agent automatisch zu konfigurieren.',
        'modal_close' => 'Schließen',
        'command_label' => 'Einsatzkommando',
        'command_helper' => 'Kopieren Sie diesen Befehl und führen Sie ihn auf Ihrem Knotenserver aus.',
        'token_success' => 'Token erfolgreich generiert',
        'token_success_body' => 'Kopieren Sie den folgenden Befehl und führen Sie ihn auf Ihrem Knoten aus.',
        'save_first' => 'Bitte speichern Sie zuerst den Knoten.',
        'auto_generated_key' => 'Automatisch generierter Knotenbereitstellungsschlüssel.',
        'error' => 'Fehler beim Generieren des Tokens. Bitte versuchen Sie es erneut.',
    ],

    'general' => [
        'na' => 'N / A',
        'unavailable' => 'Nicht verfügbar',
    ],

    'messages' => [
        'created' => 'Node wurde erfolgreich erstellt.',
        'updated' => 'Node wurde erfolgreich aktualisiert.',
        'deleted' => 'Node wurde erfolgreich gelöscht.',
        'cannot_delete_with_servers' => 'Eine Node mit aktiven Servern kann nicht gelöscht werden.',
    ],

    'allocations' => [
        'label' => 'Zuteilungen',
        'table' => [
            'ip' => 'IP',
            'port' => 'Hafen',
            'alias' => 'Alias',
            'server' => 'Server',
            'notes' => 'Notizen',
            'created' => 'Erstellt',
            'unassigned' => 'Nicht zugewiesen',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP Adresse',
                'helper' => 'Unterstützt einzelne IP oder CIDR (z.B. 192.0.2.1 oder 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Häfen',
                'helper' => 'Geben Sie Ports oder Bereiche ein (z.B. 25565, 25566, 25570-25580).',
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
            'failed' => 'Allocation Aktion fehlgeschlagen.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'Der angegebene FQDN oder die IP-Adresse kann nicht in eine gültige IP-Adresse aufgelöst werden.',
        'fqdn_required_for_ssl' => 'Ein vollqualifizierter Domainname, der in eine öffentliche IP-Adresse aufgelöst wird, ist erforderlich, um SSL für diesen Node zu verwenden.',
    ],
    'notices' => [
        'allocations_added' => 'Zuweisungen wurden diesem Node erfolgreich hinzugefügt.',
        'node_deleted' => 'Node wurde erfolgreich aus dem Panel entfernt.',
        'location_required' => 'Sie müssen mindestens einen Standort konfiguriert haben, bevor Sie einen Node zu diesem Panel hinzufügen können.',
        'node_created' => 'Neue Node erfolgreich erstellt. Sie können den Daemon auf dieser Maschine automatisch konfigurieren, indem Sie den "Konfiguration" Tab besuchen. Bevor Sie Server hinzufügen können, müssen Sie mindestens eine Allocation erstellen.',
        'node_updated' => 'Die Node-Informationen wurden aktualisiert. Wenn Daemon-Einstellungen geändert wurden, müssen Sie ihn neu starten, damit diese Änderungen wirksam werden.',
        'unallocated_deleted' => 'Alle nicht zugewiesenen Ports für <code>:ip</code> wurden gelöscht.',
    ],
];
