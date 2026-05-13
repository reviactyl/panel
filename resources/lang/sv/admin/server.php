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
        'offline' => 'Off-line',
        'starting' => 'Startande',
        'stopping' => 'Stoppar',
        'crashed' => 'Kraschade',
        'installing' => 'Installerar',
        'restoring_backup' => 'Återställer säkerhetskopia',
        'install_failed' => 'Installationen misslyckades',
        'reinstall_failed' => 'Ominstallation misslyckades',
        'suspended' => 'Upphängd',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Kärndetaljer',
            'allocation' => 'Tilldelningshantering',
            'feature_limits' => 'Applikationsfunktionsbegränsningar',
            'resources' => 'Resurshantering',
            'nest' => 'Nest-konfiguration',
            'docker' => 'Docker-konfiguration',
            'startup' => 'Startkonfiguration',
            'variables' => 'Servicevariabler',
        ],

        'fields' => [
            'name' => [
                'label' => 'Servernamn',
                'placeholder' => 'Servernamn',
                'helper' => 'Teckengränser: a-z A-Z 0-9 _ - . och utrymmen.',
            ],
            'owner' => [
                'label' => 'Serverägare',
                'helper' => 'E-postadress till serverägaren.',
            ],
            'description' => [
                'label' => 'Serverbeskrivning',
                'helper' => 'En kort beskrivning av denna server.',
            ],
            'start_on_completion' => [
                'label' => 'Starta servern när den är installerad',
            ],
            'node' => [
                'label' => 'Nod',
                'helper' => 'Noden som denna server kommer att distribueras till.',
            ],
            'allocation' => [
                'label' => 'Standardtilldelning',
                'helper' => 'Den huvudsakliga allokeringen som kommer att tilldelas denna server.',
            ],
            'additional_allocations' => [
                'label' => 'Ytterligare tilldelning(er)',
                'helper' => 'Ytterligare tilldelningar att tilldela denna server vid skapande.',
            ],
            'database_limit' => [
                'label' => 'Databasgräns',
                'helper' => 'Det totala antalet databaser som en användare får skapa för denna server.',
            ],
            'allocation_limit' => [
                'label' => 'Tilldelningsgräns',
                'helper' => 'Det totala antalet tilldelningar som en användare får skapa för denna server.',
            ],
            'backup_limit' => [
                'label' => 'Säkerhetskopieringsgräns',
                'helper' => 'Det totala antalet säkerhetskopior som kan skapas för denna server.',
            ],
            'cpu' => [
                'label' => 'CPU-gräns',
                'helper' => 'Ställ in 0 för ingen CPU-gräns. En fullständig virtuell kärna är 100 %.',
            ],
            'threads' => [
                'label' => 'CPU pinning',
                'helper' => 'Avancerat: använd ett enda nummer eller kommaseparerad lista, till exempel 0, 0-1,3 eller 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Minne',
                'helper' => 'Den maximala mängden minne som tillåts för den här behållaren. Ställ in 0 för obegränsat.',
            ],
            'swap' => [
                'label' => 'Byta',
                'helper' => 'Ställ in 0 för att inaktivera byte, eller -1 för att tillåta obegränsat byte.',
            ],
            'disk' => [
                'label' => 'Diskutrymme',
                'helper' => 'Ställ in 0 för att tillåta obegränsad diskanvändning.',
            ],
            'io' => [
                'label' => 'Block IO Vikt',
                'helper' => 'Avancerat: IO-prestanda i förhållande till andra körande containrar. Värdet ska vara 10 till 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Aktivera OOM Killer',
                'helper' => 'Avslutar servern om den bryter mot minnesgränserna.',
            ],
            'nest' => [
                'label' => 'Bo',
                'helper' => 'Välj Nest som denna server ska grupperas under.',
            ],
            'egg' => [
                'label' => 'Ägg',
                'helper' => 'Välj ägget som kommer att definiera hur denna server ska fungera.',
            ],
            'skip_scripts' => [
                'label' => 'Hoppa över Egg Install Script',
                'helper' => 'Om det valda Egget har ett installationsskript kopplat till sig, kommer skriptet att köras under installationen om inte detta är markerat.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Välj en bild från rullgardinsmenyn eller ange en anpassad bild nedan.',
            ],
            'custom_image' => [
                'label' => 'Anpassad Docker-bild',
                'placeholder' => 'Eller ange en anpassad bild...',
                'helper' => 'Detta är standard Docker-bilden som kommer att användas för att köra den här servern.',
            ],
            'startup' => [
                'label' => 'Startkommando',
                'helper' => 'Tillgängliga substitut: {{SERVER_MEMORY}}, {{SERVER_IP}} och {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Välj ett ägg för att konfigurera tjänstvariabler',
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
            'label' => 'Bo',
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
            'label' => 'Byta',
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
        'unlimited' => 'Obegränsat',
    ],

    'messages' => [
        'created' => 'Servern har skapats framgångsrikt.',
        'updated' => 'Servern har uppdaterats framgångsrikt.',
        'deleted' => 'Servern har tagits bort framgångsrikt.',
    ],

    'actions' => [
        'edit' => 'Redigera',
        'random' => 'Slumpmässig',
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
        'server_suspended' => 'Servern har :action.',
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
        'primary_allocation_updated' => 'Primär tilldelning uppdaterad.',
        'database_created' => 'Databas skapad.',
        'database_password_reset' => 'Databaslösenordsåterställning.',
        'database_deleted' => 'Databasen raderad.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Byggkonfiguration',
            'startup' => 'Uppstart',
            'manage' => 'Hantera',
        ],

        'sections' => [
            'resource_management' => 'Resurshantering',
            'application_feature_limits' => 'Applikationsfunktionsbegränsningar',
            'allocation_management' => 'Tilldelningshantering',
            'startup_command_modification' => 'Modifiering av startkommando',
            'service_configuration' => 'Servicekonfiguration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Servicevariabler',
            'reinstall_server' => 'Installera om servern',
            'install_status' => 'Installationsstatus',
            'suspend_server' => 'Stäng av servern',
            'unsuspend_server' => 'Ta bort servern',
            'transfer_server' => 'Överför server',
            'delete_server' => 'Ta bort server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Ändring av dessa värden kan utlösa en ominstallation. Servern stoppas omedelbart för den åtgärden.',
            'reinstall_server' => 'Detta kommer att installera om servern med de tilldelade tjänstskripten. Detta kan skriva över serverdata.',
            'install_status' => 'Ändra installationsstatus från avinstallerad till installerad, eller vice versa.',
            'suspend_server' => 'Detta kommer att sluta köra processer och blockera användaren från att hantera servern via panelen eller API.',
            'unsuspend_server' => 'Detta kommer att avbryta servern och återställa normal användaråtkomst.',
            'transfer_server_transferring' => 'Den här servern överförs för närvarande till en annan nod.',
            'transfer_server' => 'Överför denna server till en annan nod som är ansluten till denna panel.',
            'delete_server' => 'Detta tar permanent bort servern från panelen och agenten. Tvinga borttagning hoppar över agentborttagning om det behövs.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Servernamn',
                'helper' => 'Teckenbegränsningar: a-zA-Z0-9_-, mellanslag och standardtecken som kan skrivas ut.',
            ],
            'server_owner' => [
                'label' => 'Serverägare',
                'helper' => 'Ändring av ägarskap återkallar automatiskt daemon-tokens för den tidigare ägaren.',
            ],
            'server_description' => [
                'label' => 'Serverbeskrivning',
                'helper' => 'En kort beskrivning av denna server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (kort)',
            ],
            'external_identifier' => [
                'label' => 'Extern identifierare',
                'helper' => 'Lämna tomt för att inte tilldela en extern identifierare. Det externa ID:t ska vara unikt för denna server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'Standardanslutningsadressen som kommer att användas för den här spelservern.',
            ],
            'additional_ports' => [
                'label' => 'Ytterligare portar',
                'helper' => 'Tilldela eller ta bort extra portar. Identiska portar på olika IP-adresser kan inte tilldelas samma server.',
            ],
            'startup_command' => [
                'label' => 'Startkommando',
                'helper' => 'Tillgängligt som standard: {{SERVER_MEMORY}}, {{SERVER_IP}} och {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Standardstartkommando',
                'error' => 'FEL: Start inte definierad!',
            ],
            'cpu_limit' => [
                'label' => 'CPU-gräns',
                'helper' => 'Varje virtuell kärna är 100 %. Ställ in 0 för obegränsad CPU-tid.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU pinning',
                'helper' => 'Avancerat: lämna tomt för alla kärnor. Exempel: 0, 0-1,3 eller 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Tilldelat minne',
                'helper' => 'Den maximala mängden minne som tillåts för den här behållaren. Ställ in 0 för obegränsat.',
            ],
            'allocated_swap' => [
                'label' => 'Tilldelad Swap',
                'helper' => 'Ställ in 0 för att inaktivera byte, eller -1 för att tillåta obegränsat byte.',
            ],
            'disk_space_limit' => [
                'label' => 'Diskutrymmesgräns',
                'helper' => 'Ställ in 0 för att tillåta obegränsad diskanvändning.',
            ],
            'block_io_proportion' => [
                'label' => 'Blockera IO-proportion',
                'helper' => 'Avancerat: IO-prestanda i förhållande till andra körande containrar. Värdet ska vara 10 till 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Inaktivera OOM Killer',
                'helper' => 'Aktivering av OOM-killer kan orsaka att serverprocesser avslutas oväntat.',
            ],
            'database_limit' => [
                'label' => 'Databasgräns',
                'helper' => 'Det totala antalet databaser som en användare får skapa för denna server.',
            ],
            'allocation_limit' => [
                'label' => 'Tilldelningsgräns',
                'helper' => 'Det totala antalet tilldelningar som en användare får skapa för denna server.',
            ],
            'backup_limit' => [
                'label' => 'Säkerhetskopieringsgräns',
                'helper' => 'Det totala antalet säkerhetskopior som kan skapas för denna server.',
            ],
            'image' => [
                'label' => 'Bild',
                'helper' => 'Välj en bild från rullgardinsmenyn eller ange en anpassad bild nedan.',
            ],
            'custom_image' => [
                'label' => 'Anpassad bild',
                'placeholder' => 'Eller ange en anpassad bild...',
                'helper' => 'Detta är Docker-avbildningen som kommer att användas för att köra denna server.',
            ],
            'transfer_node' => [
                'label' => 'Nod',
                'helper' => 'Noden som denna server kommer att överföras till.',
            ],
            'transfer_allocation' => [
                'label' => 'Standardtilldelning',
                'helper' => 'Den huvudsakliga allokeringen som kommer att tilldelas denna server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Ytterligare tilldelning(er)',
                'helper' => 'Ytterligare tilldelningar att tilldela denna server vid överföring.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Installera om servern',
            'toggle_install_status' => 'Växla installationsstatus',
            'suspend_server' => 'Stäng av servern',
            'unsuspend_server' => 'Ta bort servern',
            'transfer_server' => 'Överför server',
            'confirm' => 'Bekräfta',
            'delete_server' => 'Ta bort server',
            'forcibly_delete_server' => 'Tvångsradera server',
        ],
    ],

    'allocations' => [
        'title' => 'Tilldelningar',

        'table' => [
            'ip' => 'IP',
            'port' => 'Hamn',
            'alias' => 'Alias',
            'primary' => 'Primär',
            'notes' => 'Anteckningar',
            'created' => 'Skapad',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Inget alias tilldelat',
        ],

        'actions' => [
            'make_primary' => 'Gör Primär',
        ],
    ],

    'databases' => [
        'title' => 'Databaser',

        'table' => [
            'database' => 'Databas',
            'username' => 'Användarnamn',
            'remote' => 'Avlägsen',
            'host' => 'Värd',
            'max_connections' => 'Max anslutningar',
            'created' => 'Skapad',
        ],

        'placeholder' => [
            'unlimited' => 'Obegränsat',
        ],

        'actions' => [
            'create_database' => 'Skapa databas',
            'reset_password' => 'Återställ lösenord',
            'delete' => 'Radera',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Databasnamn',
                'helper' => 'Panelen kommer att prefixa detta med server-ID, som matchar den gamla adminpanelen.',
            ],
            'database_host' => [
                'label' => 'Databasvärd',
            ],
            'remote' => [
                'label' => 'Avlägsen',
            ],
            'max_connections' => [
                'label' => 'Max anslutningar',
            ],
        ],
    ],
];
