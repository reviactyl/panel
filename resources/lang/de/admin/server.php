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
            'description' => 'Wähle die Node und die Netzwerkzuweisung für diesen Server aus.',
        ],
        'startup' => [
            'title' => 'Start',
            'description' => 'Konfiguriere das Egg, den Startbefehl und das Docker-Image.',
        ],
        'resources' => [
            'title' => 'Ressourcenlimits',
            'description' => 'Definiere die Ressourcenlimits des Servers.',
        ],
        'feature_limits' => [
            'title' => 'Funktionslimits',
            'description' => 'Begrenze Datenbanken, Zuweisungen und Backups.',
        ],
        'environment' => [
            'title' => 'Umgebungsvariablen',
            'description' => 'Lege Umgebungswerte für das ausgewählte Egg fest.',
        ],
    ],

    'status' => [
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Wird gestartet',
        'stopping' => 'Wird gestoppt',
        'crashed' => 'Abgestürzt',
        'installing' => 'Wird installiert',
        'restoring_backup' => 'Backup wird wiederhergestellt',
        'install_failed' => 'Installation fehlgeschlagen',
        'reinstall_failed' => 'Neuinstallation fehlgeschlagen',
        'suspended' => 'Suspendiert',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Kerndetails',
            'allocation' => 'Allocation-Verwaltung',
            'feature_limits' => 'Anwendungsfunktionslimits',
            'resources' => 'Ressourcenverwaltung',
            'nest' => 'Nest-Konfiguration',
            'docker' => 'Docker-Konfiguration',
            'startup' => 'Startkonfiguration',
            'variables' => 'Servicevariablen',
        ],

        'fields' => [
            'name' => [
                'label' => 'Servername',
                'placeholder' => 'Servername',
                'helper' => 'Erlaubte Zeichen: a-z A-Z 0-9 _ - . und Leerzeichen.',
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
                'label' => 'Server nach Installation starten',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'Die Node, auf der dieser Server bereitgestellt wird.',
            ],
            'allocation' => [
                'label' => 'Standard-Allocation',
                'helper' => 'Die primäre Allocation, die diesem Server zugewiesen wird.',
            ],
            'additional_allocations' => [
                'label' => 'Zusätzliche Allocation(s)',
                'helper' => 'Zusätzliche Allocations, die diesem Server bei der Erstellung zugewiesen werden.',
            ],
            'database_limit' => [
                'label' => 'Datenbanklimit',
                'helper' => 'Die maximale Anzahl an Datenbanken, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation-Limit',
                'helper' => 'Die maximale Anzahl an Allocations, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'backup_limit' => [
                'label' => 'Backup-Limit',
                'helper' => 'Die maximale Anzahl an Backups, die für diesen Server erstellt werden können.',
            ],
            'cpu' => [
                'label' => 'CPU-Limit',
                'helper' => 'Setze 0 für kein CPU-Limit. Ein voller virtueller Kern entspricht 100%.',
            ],
            'threads' => [
                'label' => 'CPU-Pinning',
                'helper' => 'Erweitert: Verwende eine einzelne Zahl oder eine kommaseparierte Liste, z.B. 0, 0-1,3 oder 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Arbeitsspeicher',
                'helper' => 'Die maximal erlaubte Speichermenge für diesen Container. Setze 0 für unbegrenzt.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Setze 0, um Swap zu deaktivieren, oder -1 für unbegrenzten Swap.',
            ],
            'disk' => [
                'label' => 'Festplattenspeicher',
                'helper' => 'Setze 0 für unbegrenzte Festplattennutzung.',
            ],
            'io' => [
                'label' => 'Block-IO-Gewichtung',
                'helper' => 'Erweitert: IO-Leistung relativ zu anderen laufenden Containern. Wert sollte zwischen 10 und 1000 liegen.',
            ],
            'oom_disabled' => [
                'label' => 'OOM Killer aktivieren',
                'helper' => 'Beendet den Server, wenn Speicherlimits überschritten werden.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Wähle das Nest aus, unter dem dieser Server gruppiert wird.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Wähle das Egg aus, das definiert, wie dieser Server funktioniert.',
            ],
            'skip_scripts' => [
                'label' => 'Egg-Installationsskript überspringen',
                'helper' => 'Wenn das ausgewählte Egg ein Installationsskript besitzt, wird dieses während der Installation ausgeführt, sofern diese Option nicht aktiviert ist.',
            ],
            'image' => [
                'label' => 'Docker-Image',
                'helper' => 'Wähle ein Image aus der Dropdown-Liste oder gib unten ein benutzerdefiniertes Image ein.',
            ],
            'custom_image' => [
                'label' => 'Benutzerdefiniertes Docker-Image',
                'placeholder' => 'Oder gib ein benutzerdefiniertes Image ein...',
                'helper' => 'Dies ist das Standard-Docker-Image, das zum Ausführen dieses Servers verwendet wird.',
            ],
            'startup' => [
                'label' => 'Startbefehl',
                'helper' => 'Verfügbare Platzhalter: {{SERVER_MEMORY}}, {{SERVER_IP}} und {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Wähle ein Egg aus, um Servicevariablen zu konfigurieren',
            ],
        ],
    ],

    'table' => [
        'unlimited' => 'Unbegrenzt',
    ],

    'alerts' => [
        'primary_allocation_updated' => 'Primäre Allocation aktualisiert.',
        'database_created' => 'Datenbank erstellt.',
        'database_password_reset' => 'Datenbankpasswort zurückgesetzt.',
        'database_deleted' => 'Datenbank gelöscht.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Informationen',
            'build_configuration' => 'Build-Konfiguration',
            'startup' => 'Start',
            'manage' => 'Verwalten',
        ],

        'sections' => [
            'resource_management' => 'Ressourcenverwaltung',
            'application_feature_limits' => 'Anwendungsfunktionslimits',
            'allocation_management' => 'Allocation-Verwaltung',
            'startup_command_modification' => 'Änderung des Startbefehls',
            'service_configuration' => 'Servicekonfiguration',
            'docker_image_configuration' => 'Docker-Image-Konfiguration',
            'service_variables' => 'Servicevariablen',
            'reinstall_server' => 'Server neu installieren',
            'install_status' => 'Installationsstatus',
            'suspend_server' => 'Server suspendieren',
            'unsuspend_server' => 'Server entsperren',
            'transfer_server' => 'Server übertragen',
            'delete_server' => 'Server löschen',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Das Ändern dieser Werte kann eine Neuinstallation auslösen. Der Server wird dafür sofort gestoppt.',
            'reinstall_server' => 'Dadurch wird der Server mit den zugewiesenen Serviceskripten neu installiert. Dies kann Serverdaten überschreiben.',
            'install_status' => 'Installationsstatus zwischen installiert und nicht installiert ändern.',
            'suspend_server' => 'Dadurch werden laufende Prozesse gestoppt und der Benutzer daran gehindert, den Server über das Panel oder die API zu verwalten.',
            'unsuspend_server' => 'Dadurch wird die Suspendierung aufgehoben und der normale Benutzerzugriff wiederhergestellt.',
            'transfer_server_transferring' => 'Dieser Server wird derzeit auf eine andere Node übertragen.',
            'transfer_server' => 'Übertrage diesen Server auf eine andere mit diesem Panel verbundene Node.',
            'delete_server' => 'Dadurch wird der Server dauerhaft aus dem Panel und dem Agent gelöscht. Erzwingtes Löschen überspringt bei Bedarf die Löschung auf dem Agent.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Servername',
                'helper' => 'Erlaubte Zeichen: a-zA-Z0-9_-, Leerzeichen und normale druckbare Zeichen.',
            ],
            'server_owner' => [
                'label' => 'Serverbesitzer',
                'helper' => 'Beim Ändern des Besitzers werden Daemon-Tokens des vorherigen Besitzers automatisch widerrufen.',
            ],
            'server_description' => [
                'label' => 'Serverbeschreibung',
                'helper' => 'Eine kurze Beschreibung dieses Servers.',
            ],
            'server_uuid' => [
                'label' => 'Server-UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server-UUID (Kurz)',
            ],
            'external_identifier' => [
                'label' => 'Externe Kennung',
                'helper' => 'Leer lassen, um keine externe Kennung zuzuweisen. Die externe ID sollte für diesen Server eindeutig sein.',
            ],
            'game_port' => [
                'label' => 'Game-Port',
                'helper' => 'Die Standardverbindungsadresse für diesen Gameserver.',
            ],
            'additional_ports' => [
                'label' => 'Zusätzliche Ports',
                'helper' => 'Zusätzliche Ports zuweisen oder entfernen. Identische Ports auf unterschiedlichen IPs können nicht demselben Server zugewiesen werden.',
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
                'helper' => 'Jeder virtuelle Kern entspricht 100 %. Setze 0 für unbegrenzte CPU-Zeit.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU-Pinning',
                'helper' => 'Erweitert: Leer lassen für alle Kerne. Beispiele: 0, 0-1,3 oder 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Zugewiesener Arbeitsspeicher',
                'helper' => 'Die maximal erlaubte Speichermenge für diesen Container. Setze 0 für unbegrenzt.',
            ],
            'allocated_swap' => [
                'label' => 'Zugewiesener Swap',
                'helper' => 'Setze 0, um Swap zu deaktivieren, oder -1 für unbegrenzten Swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Festplattenspeicherlimit',
                'helper' => 'Setze 0 für unbegrenzte Festplattennutzung.',
            ],
            'block_io_proportion' => [
                'label' => 'Block-IO-Anteil',
                'helper' => 'Erweitert: IO-Leistung relativ zu anderen laufenden Containern. Wert sollte zwischen 10 und 1000 liegen.',
            ],
            'disable_oom_killer' => [
                'label' => 'OOM Killer deaktivieren',
                'helper' => 'Das Aktivieren des OOM Killers kann dazu führen, dass Serverprozesse unerwartet beendet werden.',
            ],
            'database_limit' => [
                'label' => 'Datenbanklimit',
                'helper' => 'Die maximale Anzahl an Datenbanken, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation-Limit',
                'helper' => 'Die maximale Anzahl an Allocations, die ein Benutzer für diesen Server erstellen darf.',
            ],
            'backup_limit' => [
                'label' => 'Backup-Limit',
                'helper' => 'Die maximale Anzahl an Backups, die für diesen Server erstellt werden können.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'Die Node, auf die dieser Server übertragen wird.',
            ],
            'transfer_allocation' => [
                'label' => 'Standard-Allocation',
                'helper' => 'Die primäre Allocation, die diesem Server zugewiesen wird.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Zusätzliche Allocation(s)',
                'helper' => 'Zusätzliche Allocations, die diesem Server bei der Übertragung zugewiesen werden.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Server neu installieren',
            'toggle_install_status' => 'Installationsstatus umschalten',
            'suspend_server' => 'Server suspendieren',
            'unsuspend_server' => 'Server entsperren',
            'transfer_server' => 'Server übertragen',
            'confirm' => 'Bestätigen',
            'delete_server' => 'Server löschen',
            'forcibly_delete_server' => 'Server zwangsweise löschen',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'primary' => 'Primär',
            'notes' => 'Notizen',
            'created' => 'Erstellt',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Kein Alias zugewiesen',
        ],

        'actions' => [
            'make_primary' => 'Als primär festlegen',
        ],
    ],

    'databases' => [
        'title' => 'Datenbanken',

        'table' => [
            'database' => 'Datenbank',
            'username' => 'Benutzername',
            'remote' => 'Remote',
            'host' => 'Host',
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
                'helper' => 'Das Panel fügt die Server-ID als Präfix hinzu, wie im alten Admin-Panel.',
            ],
            'database_host' => [
                'label' => 'Datenbank-Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max. Verbindungen',
            ],
        ],
    ],
];
