<?php

return [
    'navigation' => [
        'label' => 'Monitoring',
        'group' => 'Administration',
    ],

    'page' => [
        'title' => 'Monitoring',
        'heading' => 'Live Monitoring',
    ],

    'actions' => [
        'refresh' => 'Daten aktualisieren',
    ],

    'selector' => [
        'label' => 'Node auswählen',
        'placeholder' => 'Node auswählen...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU Auslastung',
        'cpu_cores' => ':count Cores verfügbar',
        'memory_usage' => 'Arbeitsspeicher Nutzung',
        'disk_usage' => 'Speicher Nutzung',
        'network_traffic' => 'Netzwerk Traffic',
        'uptime' => 'Uptime',
        'goroutines' => ':count goroutinen',
        'last_updated' => 'Zuletzt aktualisiert',
        'no_node' => 'Keine Node ausgewählt',
        'no_node_desc' => 'Bitte wählen Sie eine Node um Monitoring Daten zu sehen',
        'no_node_hint' => 'Nutzen Sie das Dropdown Menü oben',
        'error' => 'Fehler',
        'error_desc' => 'Konnte Monitoring Daten nicht laden',
        'error_fetch' => 'Konnte Wings Daten nicht abgreifen',
        'error_node_gone' => 'Node existiert nicht mehr',
    ],

    'details' => [
        'heading' => 'System Details',
        'button' => 'Details',
        'close' => 'Schließen',
        'no_data' => 'Keine daten verfügbar. Stellen Sie sicher, dass die Node online ist.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Gesamtauslastung',
        'cpu_cores' => 'Cores',
        'per_core' => 'Pro-Core Auslastung',

        'memory_section' => 'Arbeitsspeicher',
        'total_memory' => 'Insgesamt',
        'used_memory' => 'Verwendet',
        'free_memory' => 'Frei',
        'available_memory' => 'Verfügbar',

        'swap_section' => 'Swap',
        'swap_none' => 'Swap nicht konfiguriert.',
        'swap_total' => 'Insgesamt',
        'swap_used' => 'Verwendet',
        'swap_free' => 'Frei',
        'swap_usage' => 'Auslastung',

        'network_section' => 'Netzwerk',
        'bytes_sent' => 'Bytes gesendet',
        'bytes_recv' => 'Bytes empfangen',
        'packets_sent' => 'Packets gesendet',
        'packets_received' => 'Packets empfangen',

        'runtime_section' => 'Laufzeit',
        'go_version' => 'Go Version',
        'arch' => 'Architektur',
        'goroutines' => 'Goroutinen',
        'uptime' => 'Uptime',
    ],
    'servers' => [
        'heading' => 'Server Nutzung',
        'no_node' => 'Wählen Sie eine Node aus, um Server Nutzung anzuzeigen.',
        'no_servers' => 'Keine Server auf dieser Node gefunden.',
        'error_fetch' => 'Konnte Server Daten nicht von Wings abgreifen.',
        'col' => [
            'name' => 'Server',
            'state' => 'Zustand',
            'cpu' => 'CPU',
            'memory' => 'Arbeitsspeicher',
            'disk' => 'Speicherplatz',
            'network' => 'Netzwerk',
            'uptime' => 'Uptime',
        ],
        'states' => [
            'running' => 'Running',
            'starting' => 'Starting',
            'stopping' => 'Stopping',
            'offline' => 'Offline',
            'crashed' => 'Crashed',
            'unknown' => 'Unknown',
        ],
    ],
];
