<?php

return [
    'label' => 'Server',
    'plural-label' => 'Server',

    'sections' => [
        'identity' => [
            'title' => 'Identität',
            'description' => 'Grundlegende Serverinformationen und Eigentümerschaft.',
        ],
        'allocation' => [
            'title' => 'Zuweisung',
            'description' => 'Wählen Sie die Node und die Netzwerkzuweisung für diesen Server aus.',
        ],
        'startup' => [
            'title' => 'Start',
            'description' => 'Konfigurieren Sie das Egg, den Startbefehl und das Docker-Image.',
        ],
        'resources' => [
            'title' => 'Ressourcenlimits',
            'description' => 'Definieren Sie die Server-Ressourcenlimits.',
        ],
        'feature_limits' => [
            'title' => 'Funktionslimits',
            'description' => 'Begrenzen Sie Datenbanken, Zuweisungen und Backups.',
        ],
        'environment' => [
            'title' => 'Umgebungsvariablen',
            'description' => 'Legen Sie Umgebungswerte für das ausgewählte Egg fest.',
        ],
    ],

    'status' => [
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Beginnt',
        'stopping' => 'Anhalten',
        'crashed' => 'Abgestürzt',
        'installing' => 'Installieren',
        'restoring_backup' => 'Backup wiederherstellen',
        'install_failed' => 'Installation fehlgeschlagen',
        'reinstall_failed' => 'Neuinstallation fehlgeschlagen',
        'suspended' => 'Ausgesetzt',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Kerndetails',
            'allocation' => 'Allokationsmanagement',
            'feature_limits' => 'Einschränkungen der Anwendungsfunktionen',
            'resources' => 'Ressourcenmanagement',
            'nest' => 'Nest-Konfiguration',
            'docker' => 'Docker-Konfiguration',
            'startup' => 'Startkonfiguration',
            'variables' => 'Servicevariablen',
        ],

        'fields' => [
            'name' => [
                'label' => 'Servername',
                'placeholder' => 'Servername',
                'helper' => 'Zeichenbeschränkungen: a-z A-Z 0-9 _ - . und Räume.',
            ],
            'owner' => [
                'label' => 'Serverbesitzer',
                'helper' => 'E-Mail-Adresse des Serverbesitzers.',
            ],
            'description' => [
                'label' => 'Serverbeschreibung',
                'helper' => 'Eine kurze Beschreibung dieses Servers.',
            ],
            'start_on_completion' => [
                'label' => 'Starten Sie den Server, wenn er installiert ist',
            ],
            'node' => [
                'label' => 'Knoten',
                'helper' => 'Der Knoten, auf dem dieser Server bereitgestellt wird.',
            ],
            'allocation' => [
                'label' => 'Standardzuordnung',
                'helper' => 'Die Hauptzuteilung, die diesem Server zugewiesen wird.',
            ],
            'additional_allocations' => [
                'label' => 'Zusätzliche Zuteilung(en)',
                'helper' => 'Zusätzliche Zuweisungen, die diesem Server bei der Erstellung zugewiesen werden sollen.',
            ],
            'database_limit' => [
                'label' => 'Datenbanklimit',
                'helper' => 'Die Gesamtzahl der Datenbanken, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'allocation_limit' => [
                'label' => 'Zuteilungslimit',
                'helper' => 'Die Gesamtzahl der Zuordnungen, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'backup_limit' => [
                'label' => 'Backup-Limit',
                'helper' => 'Die Gesamtzahl der Sicherungen, die für diesen Server erstellt werden können.',
            ],
            'cpu' => [
                'label' => 'CPU-Limit',
                'helper' => 'Stellen Sie 0 ein, um kein CPU-Limit zu erhalten. Ein vollständiger virtueller Kern beträgt 100 %.',
            ],
            'threads' => [
                'label' => 'CPU-Pinning',
                'helper' => 'Erweitert: Verwenden Sie eine einzelne Zahl oder eine durch Kommas getrennte Liste, zum Beispiel 0, 0-1,3 oder 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Erinnerung',
                'helper' => 'Die maximal zulässige Speichermenge für diesen Container. Stellen Sie 0 für unbegrenzt ein.',
            ],
            'swap' => [
                'label' => 'Tauschen',
                'helper' => 'Legen Sie 0 fest, um den Swap zu deaktivieren, oder -1, um unbegrenzten Swap zu ermöglichen.',
            ],
            'disk' => [
                'label' => 'Speicherplatz',
                'helper' => 'Legen Sie 0 fest, um eine unbegrenzte Festplattennutzung zu ermöglichen.',
            ],
            'io' => [
                'label' => 'E/A-Gewicht blockieren',
                'helper' => 'Erweitert: E/A-Leistung im Vergleich zu anderen laufenden Containern. Der Wert sollte zwischen 10 und 1000 liegen.',
            ],
            'oom_disabled' => [
                'label' => 'Aktivieren Sie OOM Killer',
                'helper' => 'Beendet den Server, wenn er die Speichergrenzen überschreitet.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Wählen Sie das Nest aus, unter dem dieser Server gruppiert werden soll.',
            ],
            'egg' => [
                'label' => 'Ei',
                'helper' => 'Wählen Sie das Ei aus, das definiert, wie dieser Server funktionieren soll.',
            ],
            'skip_scripts' => [
                'label' => 'Egg-Installationsskript überspringen',
                'helper' => 'Wenn dem ausgewählten Egg ein Installationsskript angehängt ist, wird das Skript während der Installation ausgeführt, sofern dies nicht aktiviert ist.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Wählen Sie ein Bild aus der Dropdown-Liste aus oder geben Sie unten ein benutzerdefiniertes Bild ein.',
            ],
            'custom_image' => [
                'label' => 'Benutzerdefiniertes Docker-Image',
                'placeholder' => 'Oder geben Sie ein benutzerdefiniertes Bild ein...',
                'helper' => 'Dies ist das Standard-Docker-Image, das zum Ausführen dieses Servers verwendet wird.',
            ],
            'startup' => [
                'label' => 'Startbefehl',
                'helper' => 'Verfügbare Ersatzprodukte: {{SERVER_MEMORY}}, {{SERVER_IP}} und {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Wählen Sie ein Ei aus, um Dienstvariablen zu konfigurieren',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Erweiterter Modus',
            'helper' => 'Aktivieren Sie diese Option, um zusätzliche Serverkonfigurationsoptionen anzuzeigen. Aktivieren Sie sie nur, wenn Sie die Auswirkungen der zusätzlichen Einstellungen verstehen.',
        ],
        'external_id' => [
            'label' => 'Externe ID',
            'helper' => 'Optional eindeutiger Bezeichner für diesen Server.',
        ],
        'owner' => [
            'label' => 'Eigentümer',
            'helper' => 'Wählen Sie den Benutzer aus, dem dieser Server gehört.',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Servername',
            'helper' => 'Ein kurzer Name für diesen Server.',
        ],
        'description' => [
            'label' => 'Beschreibung',
            'placeholder' => 'Serverbeschreibung',
            'helper' => 'Optionale Beschreibung für diesen Server.',
        ],
        'node' => [
            'label' => 'Knoten',
            'helper' => 'Die Node, auf der dieser Server bereitgestellt wird.',
        ],
        'allocation' => [
            'label' => 'Primäre Allocation',
            'helper' => 'Die Standard-IP/Port-Allocation für diesen Server.',
        ],
        'additional_allocations' => [
            'label' => 'Zusätzliche Allocations',
            'helper' => 'Optional zusätzliche Allocations zuweisen.',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'Das Service-Nest für diesen Server.',
        ],
        'egg' => [
            'label' => 'Ei',
            'helper' => 'Das Egg, das das Serververhalten definiert.',
        ],
        'startup' => [
            'label' => 'Startbefehl',
            'helper' => 'Der Startbefehl für den Server.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'helper' => 'Docker-Image, das zum Ausführen dieses Servers verwendet wird.',
            'custom' => 'Benutzerdefiniert',
        ],
        'skip_scripts' => [
            'label' => 'Egg-Skripte überspringen',
            'helper' => 'Egg-Installationsskripte während der Erstellung überspringen.',
        ],
        'start_on_completion' => [
            'label' => 'Automatisch nach Abschluss starten',
            'helper' => 'Server nach der Installation automatisch starten.',
        ],
        'memory' => [
            'label' => 'Arbeitsspeicher',
            'helper' => 'Gesamtspeicherzuweisung. Setzen Sie 0 für unbegrenzt. (Unbegrenzter Arbeitsspeicher funktioniert bei Minecraft-Eggs aufgrund des Startbefehls nicht)',
        ],
        'swap' => [
            'label' => 'Tauschen',
            'helper' => 'Swap-Speicherzuweisung. Setzen Sie 0, um Swap zu deaktivieren, oder -1, um unbegrenzten Swap zuzulassen.',
        ],
        'disk' => [
            'label' => 'Festplatte',
            'helper' => 'Festplattenplatzzuweisung. Setzen Sie 0 für unbegrenzt.',
        ],
        'io' => [
            'label' => 'IO-Gewichtung',
            'helper' => 'Relative Festplatten-I/O-Priorität (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'CPU-Begrenzung in Prozent. 100% entspricht einem vollen Kern, 200% entsprechen zwei vollen Kernen usw.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Größe in GiB eingeben',
            'helper' => 'Sie können Größen in GiB mit dem Suffix "GiB" eingeben (z.B. 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'CPU-Threads',
            'helper' => 'Optionale Zuweisung von CPU-Threads. Beispiel: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'OOM Killer deaktivieren',
            'helper' => 'Verhindert, dass der Kernel den Prozess bei Speichermangel beendet.',
        ],
        'database_limit' => [
            'label' => 'Datenbanklimit',
            'helper' => 'Maximale Anzahl von Datenbanken.',
        ],
        'allocation_limit' => [
            'label' => 'Allocationlimit',
            'helper' => 'Maximale Anzahl von Allocations.',
        ],
        'backup_limit' => [
            'label' => 'Backuplimit',
            'helper' => 'Maximale Anzahl von Backups.',
        ],
        'environment' => [
            'key' => 'Variable',
            'value' => 'Wert',
            'helper' => 'Environment-Variablen für dieses Egg.',
        ],
        'use_custom_image' => [
            'label' => 'Benutzerdefiniertes Image verwenden',
            'helper' => 'Umschalten, um ein benutzerdefiniertes Docker-Image anstelle eines vom Egg bereitgestellten zu verwenden.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'owner' => 'Besitzer',
        'node' => 'Knoten',
        'allocation' => 'Zuweisung',
        'status' => 'Status',
        'egg' => 'Ei',
        'memory' => 'Arbeitsspeicher',
        'disk' => 'Festplatte',
        'cpu' => 'CPU',
        'created' => 'Erstellt',
        'updated' => 'Aktualisiert',
        'installed' => 'Installiert',
        'no_status' => 'Kein Status',
        'unlimited' => 'Unbegrenzt',
    ],

    'messages' => [
        'created' => 'Server wurde erfolgreich erstellt.',
        'updated' => 'Server wurde erfolgreich aktualisiert.',
        'deleted' => 'Server wurde erfolgreich gelöscht.',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'random' => 'Zufällig',
        'toggle_install_status' => 'Installationsstatus umschalten',
        'suspend' => 'Suspendieren',
        'unsuspend' => 'Reaktivieren',
        'suspended' => 'Suspendiert',
        'unsuspended' => 'Reaktiviert',
        'reinstall' => 'Neu installieren',
        'delete' => 'Löschen',
        'delete_forcibly' => 'Erzwingend löschen',
        'view' => 'Ansehen',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Sie versuchen, die Standardzuweisung für diesen Server zu löschen, aber es gibt keine Fallback-Zuweisung.',
        'marked_as_failed' => 'Dieser Server wurde als fehlgeschlagen bei einer vorherigen Installation markiert. Der aktuelle Status kann in diesem Zustand nicht umgeschaltet werden.',
        'bad_variable' => 'Es gab einen Validierungsfehler mit der Variable :name.',
        'daemon_exception' => 'Es trat ein Fehler bei der Kommunikation mit dem Daemon auf, der zu einem HTTP/:code Antwortcode führte. Dieser Fehler wurde protokolliert. (Anfrage-ID: :request_id)',
        'default_allocation_not_found' => 'Die angeforderte Standardzuweisung wurde in den Zuweisungen dieses Servers nicht gefunden.',
    ],

    'alerts' => [
        'install_toggled' => 'Server-Installationsstatus wurde umgeschaltet.',
        'server_suspended' => 'Server wurde :action.',
        'server_reinstalled' => 'Server-Neuinstallation wurde gestartet.',
        'server_deleted' => 'Server wurde gelöscht.',
        'server_delete_failed' => 'Server konnte nicht gelöscht werden.',
        'startup_changed' => 'Die Startkonfiguration für diesen Server wurde aktualisiert. Wenn das Nest oder Ei dieses Servers geändert wurde, findet jetzt eine Neuinstallation statt.',
        'server_created' => 'Der Server wurde erfolgreich auf dem Panel erstellt. Bitte geben Sie dem Daemon einige Minuten Zeit, um diesen Server vollständig zu installieren.',
        'build_updated' => 'Die Build-Details für diesen Server wurden aktualisiert. Einige Änderungen erfordern möglicherweise einen Neustart, um wirksam zu werden.',
        'suspension_toggled' => 'Der Suspendierungsstatus des Servers wurde auf :status geändert.',
        'rebuild_on_boot' => 'Dieser Server wurde so markiert, dass er einen Docker-Container-Rebuild erfordert. Dies geschieht beim nächsten Start des Servers.',
        'details_updated' => 'Serverdetails wurden erfolgreich aktualisiert.',
        'docker_image_updated' => 'Das Standard-Docker-Image für diesen Server wurde erfolgreich geändert. Ein Neustart ist erforderlich, um diese Änderung zu übernehmen.',
        'node_required' => 'Sie müssen mindestens einen Node konfiguriert haben, bevor Sie diesem Panel einen Server hinzufügen können.',
        'transfer_nodes_required' => 'Sie müssen mindestens zwei Nodes konfiguriert haben, bevor Sie Server übertragen können.',
        'transfer_started' => 'Serverübertragung wurde gestartet.',
        'transfer_not_viable' => 'Der ausgewählte Node verfügt nicht über den erforderlichen Speicherplatz oder Arbeitsspeicher, um diesen Server aufzunehmen.',
        'primary_allocation_updated' => 'Primäre Zuordnung aktualisiert.',
        'database_created' => 'Datenbank erstellt.',
        'database_password_reset' => 'Zurücksetzen des Datenbankkennworts.',
        'database_deleted' => 'Datenbank gelöscht.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build-Konfiguration',
            'startup' => 'Start-up',
            'manage' => 'Verwalten',
        ],

        'sections' => [
            'resource_management' => 'Ressourcenmanagement',
            'application_feature_limits' => 'Einschränkungen der Anwendungsfunktionen',
            'allocation_management' => 'Allokationsmanagement',
            'startup_command_modification' => 'Änderung des Startbefehls',
            'service_configuration' => 'Dienstkonfiguration',
            'docker_image_configuration' => 'Docker-Image-Konfiguration',
            'service_variables' => 'Servicevariablen',
            'reinstall_server' => 'Server neu installieren',
            'install_status' => 'Installationsstatus',
            'suspend_server' => 'Server anhalten',
            'unsuspend_server' => 'Suspendierung des Servers aufheben',
            'transfer_server' => 'Transferserver',
            'delete_server' => 'Server löschen',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Eine Änderung dieser Werte kann eine Neuinstallation auslösen. Der Server wird für diesen Vorgang sofort gestoppt.',
            'reinstall_server' => 'Dadurch wird der Server mit den zugewiesenen Dienstskripten neu installiert. Dadurch könnten Serverdaten überschrieben werden.',
            'install_status' => 'Ändern Sie den Installationsstatus von „Deinstalliert“ in „Installiert“ oder umgekehrt.',
            'suspend_server' => 'Dadurch werden laufende Prozesse gestoppt und der Benutzer daran gehindert, den Server über das Panel oder die API zu verwalten.',
            'unsuspend_server' => 'Dadurch wird die Suspendierung des Servers aufgehoben und der normale Benutzerzugriff wiederhergestellt.',
            'transfer_server_transferring' => 'Dieser Server wird derzeit auf einen anderen Knoten übertragen.',
            'transfer_server' => 'Übertragen Sie diesen Server auf einen anderen Knoten, der mit diesem Panel verbunden ist.',
            'delete_server' => 'Dadurch wird der Server dauerhaft aus dem Panel und dem Agenten gelöscht. „Löschen erzwingen“ überspringt bei Bedarf das Löschen des Agenten.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Servername',
                'helper' => 'Zeichenbeschränkungen: a-zA-Z0-9_-, Leerzeichen und druckbare Standardzeichen.',
            ],
            'server_owner' => [
                'label' => 'Serverbesitzer',
                'helper' => 'Beim Ändern des Besitzers werden Daemon-Tokens automatisch für den vorherigen Besitzer widerrufen.',
            ],
            'server_description' => [
                'label' => 'Serverbeschreibung',
                'helper' => 'Eine kurze Beschreibung dieses Servers.',
            ],
            'server_uuid' => [
                'label' => 'Server-UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server-UUID (kurz)',
            ],
            'external_identifier' => [
                'label' => 'Externer Bezeichner',
                'helper' => 'Leer lassen, um keine externe Kennung zuzuweisen. Die externe ID sollte für diesen Server eindeutig sein.',
            ],
            'game_port' => [
                'label' => 'Game-Port',
                'helper' => 'Die Standardverbindungsadresse, die für diesen Spieleserver verwendet wird.',
            ],
            'additional_ports' => [
                'label' => 'Zusätzliche Ports',
                'helper' => 'Weisen Sie zusätzliche Ports zu oder entfernen Sie sie. Identische Ports auf verschiedenen IPs können nicht demselben Server zugewiesen werden.',
            ],
            'startup_command' => [
                'label' => 'Startbefehl',
                'helper' => 'Standardmäßig verfügbar: {{SERVER_MEMORY}}, {{SERVER_IP}} und {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Standard-Startbefehl',
                'error' => 'FEHLER: Start nicht definiert!',
            ],
            'cpu_limit' => [
                'label' => 'CPU-Limit',
                'helper' => 'Jeder virtuelle Kern ist 100 %. Stellen Sie 0 für uneingeschränkte CPU-Zeit ein.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU-Pinning',
                'helper' => 'Erweitert: Für alle Kerne leer lassen. Beispiele: 0, 0-1,3 oder 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Zugewiesener Speicher',
                'helper' => 'Die maximal zulässige Speichermenge für diesen Container. Stellen Sie 0 für unbegrenzt ein.',
            ],
            'allocated_swap' => [
                'label' => 'Zugewiesener Swap',
                'helper' => 'Legen Sie 0 fest, um den Swap zu deaktivieren, oder -1, um unbegrenzten Swap zu ermöglichen.',
            ],
            'disk_space_limit' => [
                'label' => 'Speicherplatzbegrenzung',
                'helper' => 'Legen Sie 0 fest, um eine unbegrenzte Festplattennutzung zu ermöglichen.',
            ],
            'block_io_proportion' => [
                'label' => 'Block-IO-Anteil',
                'helper' => 'Erweitert: E/A-Leistung im Vergleich zu anderen laufenden Containern. Der Wert sollte zwischen 10 und 1000 liegen.',
            ],
            'disable_oom_killer' => [
                'label' => 'Deaktivieren Sie OOM Killer',
                'helper' => 'Die Aktivierung des OOM-Killers kann dazu führen, dass Serverprozesse unerwartet beendet werden.',
            ],
            'database_limit' => [
                'label' => 'Datenbanklimit',
                'helper' => 'Die Gesamtzahl der Datenbanken, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'allocation_limit' => [
                'label' => 'Zuteilungslimit',
                'helper' => 'Die Gesamtzahl der Zuordnungen, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'backup_limit' => [
                'label' => 'Backup-Limit',
                'helper' => 'Die Gesamtzahl der Sicherungen, die für diesen Server erstellt werden können.',
            ],
            'image' => [
                'label' => 'Bild',
                'helper' => 'Wählen Sie ein Bild aus der Dropdown-Liste aus oder geben Sie unten ein benutzerdefiniertes Bild ein.',
            ],
            'custom_image' => [
                'label' => 'Benutzerdefiniertes Bild',
                'placeholder' => 'Oder geben Sie ein benutzerdefiniertes Bild ein...',
                'helper' => 'Dies ist das Docker-Image, das zum Ausführen dieses Servers verwendet wird.',
            ],
            'transfer_node' => [
                'label' => 'Knoten',
                'helper' => 'Der Knoten, auf den dieser Server übertragen wird.',
            ],
            'transfer_allocation' => [
                'label' => 'Standardzuordnung',
                'helper' => 'Die Hauptzuteilung, die diesem Server zugewiesen wird.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Zusätzliche Zuteilung(en)',
                'helper' => 'Zusätzliche Zuweisungen, die diesem Server bei der Übertragung zugewiesen werden sollen.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Server neu installieren',
            'toggle_install_status' => 'Installationsstatus umschalten',
            'suspend_server' => 'Server anhalten',
            'unsuspend_server' => 'Suspendierung des Servers aufheben',
            'transfer_server' => 'Transferserver',
            'confirm' => 'Bestätigen',
            'delete_server' => 'Server löschen',
            'forcibly_delete_server' => 'Server zwangsweise löschen',
        ],
    ],

    'allocations' => [
        'title' => 'Zuteilungen',

        'table' => [
            'ip' => 'IP',
            'port' => 'Hafen',
            'alias' => 'Alias',
            'primary' => 'Primär',
            'notes' => 'Notizen',
            'created' => 'Erstellt',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Kein Alias ​​zugewiesen',
        ],

        'actions' => [
            'make_primary' => 'Machen Sie primär',
        ],
    ],

    'databases' => [
        'title' => 'Datenbanken',

        'table' => [
            'database' => 'Datenbank',
            'username' => 'Benutzername',
            'remote' => 'Fernbedienung',
            'host' => 'Gastgeber',
            'max_connections' => 'Max. Verbindungen',
            'created' => 'Erstellt',
        ],

        'placeholder' => [
            'unlimited' => 'Unbegrenzt',
        ],

        'actions' => [
            'create_database' => 'Datenbank erstellen',
            'reset_password' => 'Passwort zurücksetzen',
            'delete' => 'Löschen',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Datenbankname',
                'helper' => 'Das Panel stellt diesem die Server-ID voran, passend zum alten Admin-Panel.',
            ],
            'database_host' => [
                'label' => 'Datenbankhost',
            ],
            'remote' => [
                'label' => 'Fernbedienung',
            ],
            'max_connections' => [
                'label' => 'Max. Verbindungen',
            ],
        ],
    ],
];
