<?php

return [
    'label' => 'Server',
    'plural-label' => 'Servrar',

    'sections' => [
        'identity' => [
            'title' => 'Identitet',
            'description' => 'Grundläggande serverinformation och ägarskap.',
        ],
        'allocation' => [
            'title' => 'Allokering',
            'description' => 'Välj nod och nätverksallokering för denna server.',
        ],
        'startup' => [
            'title' => 'Uppstart',
            'description' => 'Konfigurera egg, startkommando och Docker-image.',
        ],
        'resources' => [
            'title' => 'Resursgränser',
            'description' => 'Ange serverns resursgränser.',
        ],
        'feature_limits' => [
            'title' => 'Funktionsgränser',
            'description' => 'Begränsa databaser, allokeringar och säkerhetskopior.',
        ],
        'environment' => [
            'title' => 'Miljövariabler',
            'description' => 'Ange miljövärden för valt egg.',
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
            'label' => 'Avancerat läge',
            'helper' => 'Aktivera för att visa ytterligare serverinställningar. Aktivera endast om du förstår konsekvenserna av de extra inställningarna.',
        ],
        'external_id' => [
            'label' => 'Externt ID',
            'helper' => 'Valfri unik identifierare för denna server.',
        ],
        'owner' => [
            'label' => 'Ägare',
            'helper' => 'Välj användaren som äger denna server.',
        ],
        'name' => [
            'label' => 'Namn',
            'placeholder' => 'Servernamn',
            'helper' => 'Ett kort namn för denna server.',
        ],
        'description' => [
            'label' => 'Beskrivning',
            'placeholder' => 'Serverbeskrivning',
            'helper' => 'Valfri beskrivning för denna server.',
        ],
        'node' => [
            'label' => 'Nod',
            'helper' => 'Noden som denna server kommer att distribueras till.',
        ],
        'allocation' => [
            'label' => 'Primär allokering',
            'helper' => 'Standard IP/port-allokering för denna server.',
        ],
        'additional_allocations' => [
            'label' => 'Ytterligare allokeringar',
            'helper' => 'Valfria extra allokeringar att tilldela.',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'Tjänstens nest för denna server.',
        ],
        'egg' => [
            'label' => 'Ägg',
            'helper' => 'Ägg som definierar serverns beteende.',
        ],
        'startup' => [
            'label' => 'Startkommando',
            'helper' => 'Startkommandot för servern.',
        ],
        'image' => [
            'label' => 'Docker-image',
            'helper' => 'Docker-image som används för att köra denna server.',
            'custom' => 'Anpassad',
        ],
        'skip_scripts' => [
            'label' => 'Hoppa över ägg-skript',
            'helper' => 'Hoppa över ägg-installationsskript vid skapande.',
        ],
        'start_on_completion' => [
            'label' => 'Starta vid slutförande',
            'helper' => 'Starta servern automatiskt efter installation.',
        ],
        'memory' => [
            'label' => 'Minne',
            'helper' => 'Total minnesallokering. Ange 0 för obegränsat. (Obegränsat minne fungerar inte för Minecraft-eggs på grund av startkommandot)',
        ],
        'swap' => [
            'label' => 'Swap',
            'helper' => 'Swap-minnesallokering. Ange 0 för att inaktivera swap eller -1 för obegränsad swap.',
        ],
        'disk' => [
            'label' => 'Disk',
            'helper' => 'Diskutrymmesallokering. Ange 0 för obegränsat.',
        ],
        'io' => [
            'label' => 'I/O-prioritet',
            'helper' => 'Relativ disk-I/O-prioritet (10–1000).',
        ],
        'cpu' => [
            'label' => 'Processor',
            'helper' => 'Processor-gräns i procent. 100 % motsvarar en hel kärna, 200 % motsvarar två hela kärnor, osv.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Ange storlek i GiB',
            'helper' => 'Du kan ange storlekar i GiB genom att använda suffixet "GiB" (t.ex. 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Processor-trådar',
            'helper' => 'Valfri trådbindning. Exempel: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Inaktivera OOM Killer',
            'helper' => 'Förhindra att kärnan avslutar processen vid minnesbrist.',
        ],
        'database_limit' => [
            'label' => 'Databasgräns',
            'helper' => 'Maximalt antal databaser.',
        ],
        'allocation_limit' => [
            'label' => 'Allokeringsgräns',
            'helper' => 'Maximalt antal allokeringar.',
        ],
        'backup_limit' => [
            'label' => 'Säkerhetskopieringsgräns',
            'helper' => 'Maximalt antal säkerhetskopior.',
        ],
        'environment' => [
            'key' => 'Variabel',
            'value' => 'Värde',
            'helper' => 'Miljövariabler för detta egg.',
        ],
        'use_custom_image' => [
            'label' => 'Använd anpassad image',
            'helper' => 'Aktivera för att använda en anpassad Docker-image istället för den som tillhandahålls av egg.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Namn',
        'owner' => 'Ägare',
        'node' => 'Nod',
        'allocation' => 'Allokering',
        'status' => 'Status',
        'egg' => 'Ägg',
        'memory' => 'Minne',
        'disk' => 'Disk',
        'cpu' => 'Processor',
        'created' => 'Skapad',
        'updated' => 'Uppdaterad',
        'installed' => 'Installerad',
        'no_status' => 'Ingen status',
        'unlimited' => 'Unlimited',
    ],

    'messages' => [
        'created' => 'Servern har skapats framgångsrikt.',
        'updated' => 'Servern har uppdaterats framgångsrikt.',
        'deleted' => 'Servern har tagits bort framgångsrikt.',
    ],

    'actions' => [
        'edit' => 'Redigera',
        'random' => 'Random',
        'toggle_install_status' => 'Växla installationsstatus',
        'suspend' => 'Stäng av',
        'unsuspend' => 'Återaktivera',
        'suspended' => 'Avstängd',
        'unsuspended' => 'Aktiv',
        'reinstall' => 'Installera om',
        'delete' => 'Ta bort',
        'delete_forcibly' => 'Tvinga borttagning',
        'view' => 'Visa',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Du försöker ta bort standardallokeringen för denna server men det finns ingen reservallokering att använda.',
        'marked_as_failed' => 'Denna server markerades som misslyckad vid en tidigare installation. Nuvarande status kan inte växlas i detta tillstånd.',
        'bad_variable' => 'Det uppstod ett valideringsfel med variabeln :name.',
        'daemon_exception' => 'Ett undantag uppstod när systemet försökte kommunicera med daemonen vilket resulterade i en HTTP/:code svarskod. Detta undantag har loggats. (förfrågnings-id: :request_id)',
        'default_allocation_not_found' => 'Den begärda standardallokeringen hittades inte i denna servers allokeringar.',
    ],

    'alerts' => [
        'install_toggled' => 'Serverns installationsstatus har ändrats.',
        'server_suspended' => 'Åtgärd har utförts på servern.',
        'server_reinstalled' => 'Ominstallation av servern har påbörjats.',
        'server_deleted' => 'Servern har tagits bort.',
        'server_delete_failed' => 'Misslyckades med att ta bort servern.',
        'startup_changed' => 'Startkonfigurationen för denna server har uppdaterats. Om denna servers näste eller ägg ändrades kommer en ominstallation att ske nu.',
        'server_created' => 'Servern skapades framgångsrikt i panelen. Vänligen ge daemonen några minuter att helt installera denna server.',
        'build_updated' => 'Byggdetaljerna för denna server har uppdaterats. Vissa ändringar kan kräva en omstart för att träda i kraft.',
        'suspension_toggled' => 'Serverns avstängningsstatus har ändrats till :status.',
        'rebuild_on_boot' => 'Denna server har markerats som att den kräver en Docker Container-återuppbyggnad. Detta kommer att ske nästa gång servern startas.',
        'details_updated' => 'Serverdetaljer har uppdaterats framgångsrikt.',
        'docker_image_updated' => 'Standard Docker-avbildningen för denna server har ändrats framgångsrikt. En omstart krävs för att tillämpa denna ändring.',
        'node_required' => 'Du måste ha minst en nod konfigurerad innan du kan lägga till en server i denna panel.',
        'transfer_nodes_required' => 'Du måste ha minst två noder konfigurerade innan du kan överföra servrar.',
        'transfer_started' => 'Serveröverföringen har startats.',
        'transfer_not_viable' => 'Noden du valde har inte tillräckligt med diskutrymme eller minne för att rymma denna server.',
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
