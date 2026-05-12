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
        'starting' => 'Starting',
        'stopping' => 'Stopping',
        'crashed' => 'Crashed',
        'installing' => 'Installing',
        'restoring_backup' => 'Restoring Backup',
        'install_failed' => 'Install Failed',
        'reinstall_failed' => 'Reinstall Failed',
        'suspended' => 'Suspended',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Core Details',
            'allocation' => 'Allocation Management',
            'feature_limits' => 'Application Feature Limits',
            'resources' => 'Resource Management',
            'nest' => 'Nest Configuration',
            'docker' => 'Docker Configuration',
            'startup' => 'Startup Configuration',
            'variables' => 'Service Variables',
        ],

        'fields' => [
            'name' => [
                'label' => 'Server Name',
                'placeholder' => 'Server Name',
                'helper' => 'Character limits: a-z A-Z 0-9 _ - . and spaces.',
            ],
            'owner' => [
                'label' => 'Server Owner',
                'helper' => 'Email address of the Server Owner.',
            ],
            'description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'start_on_completion' => [
                'label' => 'Start Server when Installed',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be deployed to.',
            ],
            'allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on creation.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'cpu' => [
                'label' => 'CPU Limit',
                'helper' => 'Set 0 for no CPU limit. A full virtual core is 100%.',
            ],
            'threads' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: use a single number or comma separated list, for example 0, 0-1,3, or 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk' => [
                'label' => 'Disk Space',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Enable OOM Killer',
                'helper' => 'Terminates the server if it breaches memory limits.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Select the Nest that this server will be grouped under.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Select the Egg that will define how this server should operate.',
            ],
            'skip_scripts' => [
                'label' => 'Skip Egg Install Script',
                'helper' => 'If the selected Egg has an install script attached to it, the script will run during install unless this is checked.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Docker Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the default Docker image that will be used to run this server.',
            ],
            'startup' => [
                'label' => 'Startup Command',
                'helper' => 'Available substitutes: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Select an egg to configure service variables',
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
            'placeholder' => 'Server Name',
            'helper' => 'Ein kurzer Name für diesen Server.',
        ],
        'description' => [
            'label' => 'Beschreibung',
            'placeholder' => 'Serverbeschreibung',
            'helper' => 'Optionale Beschreibung für diesen Server.',
        ],
        'node' => [
            'label' => 'Node',
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
            'label' => 'Egg',
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
            'label' => 'Swap',
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
            'label' => 'CPU Threads',
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
        'node' => 'Node',
        'allocation' => 'Allocation',
        'status' => 'Status',
        'egg' => 'Egg',
        'memory' => 'Arbeitsspeicher',
        'disk' => 'Festplatte',
        'cpu' => 'CPU',
        'created' => 'Erstellt',
        'updated' => 'Aktualisiert',
        'installed' => 'Installiert',
        'no_status' => 'Kein Status',
        'unlimited' => 'Unlimited',
    ],

    'messages' => [
        'created' => 'Server wurde erfolgreich erstellt.',
        'updated' => 'Server wurde erfolgreich aktualisiert.',
        'deleted' => 'Server wurde erfolgreich gelöscht.',
    ],

    'actions' => [
        'edit' => 'Bearbeiten',
        'random' => 'Random',
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
        'primary_allocation_updated' => 'Primary allocation updated.',
        'database_created' => 'Database created.',
        'database_password_reset' => 'Database password reset.',
        'database_deleted' => 'Database deleted.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build Configuration',
            'startup' => 'Startup',
            'manage' => 'Manage',
        ],

        'sections' => [
            'resource_management' => 'Resource Management',
            'application_feature_limits' => 'Application Feature Limits',
            'allocation_management' => 'Allocation Management',
            'startup_command_modification' => 'Startup Command Modification',
            'service_configuration' => 'Service Configuration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Service Variables',
            'reinstall_server' => 'Reinstall Server',
            'install_status' => 'Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'delete_server' => 'Delete Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Changing these values can trigger a reinstall. The server will be stopped immediately for that operation.',
            'reinstall_server' => 'This will reinstall the server with the assigned service scripts. This could overwrite server data.',
            'install_status' => 'Change install status from uninstalled to installed, or vice versa.',
            'suspend_server' => 'This will stop running processes and block the user from managing the server through the panel or API.',
            'unsuspend_server' => 'This will unsuspend the server and restore normal user access.',
            'transfer_server_transferring' => 'This server is currently being transferred to another node.',
            'transfer_server' => 'Transfer this server to another node connected to this panel.',
            'delete_server' => 'This permanently deletes the server from the panel and Agent. Force delete skips Agent deletion if necessary.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Server Name',
                'helper' => 'Character limits: a-zA-Z0-9_-, spaces, and standard printable characters.',
            ],
            'server_owner' => [
                'label' => 'Server Owner',
                'helper' => 'Changing ownership automatically revokes daemon tokens for the previous owner.',
            ],
            'server_description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (Short)',
            ],
            'external_identifier' => [
                'label' => 'External Identifier',
                'helper' => 'Leave empty to not assign an external identifier. The external ID should be unique to this server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'The default connection address that will be used for this game server.',
            ],
            'additional_ports' => [
                'label' => 'Additional Ports',
                'helper' => 'Assign or remove extra ports. Identical ports on different IPs cannot be assigned to the same server.',
            ],
            'startup_command' => [
                'label' => 'Startup Command',
                'helper' => 'Available by default: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Default Startup Command',
                'error' => 'ERROR: Startup Not Defined!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Limit',
                'helper' => 'Each virtual core is 100%. Set 0 for unrestricted CPU time.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: leave blank for all cores. Examples: 0, 0-1,3, or 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Allocated Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'allocated_swap' => [
                'label' => 'Allocated Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Space Limit',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'block_io_proportion' => [
                'label' => 'Block IO Proportion',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Disable OOM Killer',
                'helper' => 'Enabling OOM killer may cause server processes to exit unexpectedly.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the Docker image that will be used to run this server.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be transferred to.',
            ],
            'transfer_allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstall Server',
            'toggle_install_status' => 'Toggle Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'confirm' => 'Confirm',
            'delete_server' => 'Delete Server',
            'forcibly_delete_server' => 'Forcibly Delete Server',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primary',
            'notes' => 'Notes',
            'created' => 'Created',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'No Alias Assigned',
        ],

        'actions' => [
            'make_primary' => 'Make Primary',
        ],
    ],

    'databases' => [
        'title' => 'Databases',

        'table' => [
            'database' => 'Database',
            'username' => 'Username',
            'remote' => 'Remote',
            'host' => 'Host',
            'max_connections' => 'Max Connections',
            'created' => 'Created',
        ],

        'placeholder' => [
            'unlimited' => 'Unlimited',
        ],

        'actions' => [
            'create_database' => 'Create Database',
            'reset_password' => 'Reset Password',
            'delete' => 'Delete',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Database Name',
                'helper' => 'The panel will prefix this with the server ID, matching the old admin panel.',
            ],
            'database_host' => [
                'label' => 'Database Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max Connections',
            ],
        ],
    ],
];
