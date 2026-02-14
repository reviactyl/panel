<?php

return [
    'label' => 'Nod',
    'plural-label' => 'Noder',

    'sections' => [
        'identity' => [
            'title' => 'Identitet',
            'description' => 'Grundläggande information om noden.',
        ],
        'connection' => [
            'title' => 'Anslutningsdetaljer',
            'description' => 'Konfigurera hur anslutning till denna nod ska ske.',
        ],
        'resources' => [
            'title' => 'Resursallokering',
            'description' => 'Ange minnes- och disklimitar för denna nod.',
        ],
        'daemon' => [
            'title' => 'Daemon-konfiguration',
            'description' => 'Konfigurera daemon-specifika inställningar.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Publik',
            'helper' => 'Genom att sätta en nod som privat nekar du möjligheten till automatisk distribution till denna nod.',
        ],
        'name' => [
            'label' => 'Namn',
            'placeholder' => 'Nodnamn',
            'helper' => 'Ett beskrivande namn för denna nod.',
        ],
        'description' => [
            'label' => 'Beskrivning',
            'placeholder' => 'Nodbeskrivning',
            'helper' => 'Valfri beskrivning för denna nod.',
        ],
        'location' => [
            'label' => 'Plats',
            'helper' => 'Platsen som denna nod är tilldelad till.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.se',
            'helper' => 'Fullständigt kvalificerat domännamn eller IP-adress.',
        ],
        'ssl' => [
            'label' => 'Använder SSL',
            'helper' => 'Om daemonen på denna nod är konfigurerad att använda SSL för säker kommunikation.',
            'helper_forced' => 'Panelen körs via HTTPS, därför är SSL tvingat för denna nod.',
        ],
        'behind_proxy' => [
            'label' => 'Bakom proxy',
            'helper' => 'Aktivera om denna nod är bakom en proxy som Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Underhållsläge',
            'helper' => 'Förhindra att nya servrar skapas på denna nod.',
        ],
        'memory' => [
            'label' => 'Totalt minne',
            'helper' => 'Totalt minne i MiB tillgängligt på denna nod.',
        ],
        'memory_overallocate' => [
            'label' => 'Minnesövertilldelning',
            'helper' => 'Procentandel minne som får övertilldelas. Använd -1 för att inaktivera kontroll.',
        ],
        'disk' => [
            'label' => 'Totalt diskutrymme',
            'helper' => 'Totalt diskutrymme i MiB tillgängligt på denna nod.',
        ],
        'disk_overallocate' => [
            'label' => 'Diskövertilldelning',
            'helper' => 'Procentandel disk som får övertilldelas. Använd -1 för att inaktivera kontroll.',
        ],
        'upload_size' => [
            'label' => 'Maximal uppladdningsstorlek',
            'helper' => 'Maximal filstorlek som får laddas upp via webbpanelen.',
        ],
        'daemon_base' => [
            'label' => 'Bas-katalog',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Katalog där serverfiler lagras.',
        ],
        'daemon_listen' => [
            'label' => 'Daemon-port',
            'helper' => 'Porten som daemonen lyssnar på för HTTP-kommunikation.',
        ],
        'daemon_sftp' => [
            'label' => 'SFTP-port',
            'helper' => 'Porten som används för SFTP-anslutningar.',
        ],
        'daemon_token_id' => [
            'label' => 'Token-ID',
        ],
        'container_text' => [
            'label' => 'Containerprefix',
            'helper' => 'Textprefix som visas i containernamn.',
        ],
        'daemon_text' => [
            'label' => 'Daemonprefix',
            'helper' => 'Textprefix som visas i daemon-loggar.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Namn',
        'location' => 'Plats',
        'fqdn' => 'FQDN',
        'scheme' => 'Protokoll',
        'public' => 'Publik',
        'behind_proxy' => 'Bakom proxy',
        'maintenance_mode' => 'Underhåll',
        'memory' => 'Minne',
        'memory_overallocate' => 'Minnesövertilldelning',
        'disk' => 'Disk',
        'disk_overallocate' => 'Diskövertilldelning',
        'upload_size' => 'Uppladdningsstorlek',
        'daemon_listen' => 'Daemon-port',
        'daemon_sftp' => 'SFTP-port',
        'daemon_base' => 'Bas-katalog',
        'servers' => 'Servrar',
        'created' => 'Skapad',
        'updated' => 'Uppdaterad',
    ],

    'actions' => [
        'create' => 'Create',
        'edit' => 'Redigera',
        'delete' => 'Ta bort',
        'view' => 'Visa',
    ],

    'messages' => [
        'created' => 'Noden har skapats framgångsrikt.',
        'updated' => 'Noden har uppdaterats framgångsrikt.',
        'deleted' => 'Noden har tagits bort framgångsrikt.',
        'cannot_delete_with_servers' => 'Kan inte ta bort en nod med aktiva servrar.',
    ],

    'allocations' => [
        'label' => 'Allocations',
        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'server' => 'Server',
            'notes' => 'Anteckningar',
            'created' => 'Skapad',
            'unassigned' => 'Otilldelad',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'IP-adress',
                'helper' => 'Stöder enskild IP eller CIDR (t.ex. 192.0.2.1 eller 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Portar',
                'helper' => 'Ange portar eller intervall (t.ex. 25565, 25566, 25570–25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP-alias',
                'helper' => 'Valfritt alias att visa istället för IP-adressen.',
            ],
        ],
        'actions' => [
            'add' => 'Lägg till allokering',
            'delete' => 'Ta bort',
        ],
        'messages' => [
            'created' => 'Allokeringar har lagts till.',
            'deleted' => 'Allokering har tagits bort.',
            'failed' => 'Åtgärden för allokering misslyckades.',
        ],
    ],
    
    'validation' => [
        'fqdn_not_resolvable' => 'Det angivna FQDN eller IP-adressen löser inte till en giltig IP-adress.',
        'fqdn_required_for_ssl' => 'Ett fullständigt kvalificerat domännamn som löser till en offentlig IP-adress krävs för att använda SSL för denna nod.',
    ],
    'notices' => [
        'allocations_added' => 'Allokeringar har lagts till framgångsrikt på denna nod.',
        'node_deleted' => 'Noden har tagits bort framgångsrikt från panelen.',
        'location_required' => 'Du måste ha minst en plats konfigurerad innan du kan lägga till en nod i denna panel.',
        'node_created' => 'Ny nod skapad framgångsrikt. Du kan automatiskt konfigurera daemonen på denna maskin genom att besöka fliken \'Konfiguration\'. <strong>Innan du kan lägga till några servrar måste du först allokera minst en IP-adress och port.</strong>',
        'node_updated' => 'Nodinformationen har uppdaterats. Om några daemoninställningar ändrades behöver du starta om den för att dessa ändringar ska träda i kraft.',
        'unallocated_deleted' => 'Alla icke-allokerade portar för <code>:ip</code> har tagits bort.',
    ],
];
