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
            'placeholder' => 't.ex. ghcr.io/pterodactyl/yolks:java_17',
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
    ],

    'messages' => [
        'created' => 'Servern har skapats framgångsrikt.',
        'updated' => 'Servern har uppdaterats framgångsrikt.',
        'deleted' => 'Servern har tagits bort framgångsrikt.',
    ],

    'actions' => [
        'edit' => 'Redigera',
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
    ],
];
