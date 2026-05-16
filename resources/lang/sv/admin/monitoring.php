<?php

return [
    'navigation' => [
        'label' => 'Övervakning',
        'group' => 'Administration',
    ],

    'page' => [
        'title' => 'Övervakning',
        'heading' => 'Liveövervakning',
    ],

    'actions' => [
        'refresh' => 'Uppdatera data',
    ],

    'selector' => [
        'label' => 'Välj Nod',
        'placeholder' => 'Välj en nod...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU-användning',
        'cpu_cores' => ':count-kärnor tillgängliga',
        'memory_usage' => 'Minnesanvändning',
        'disk_usage' => 'Diskanvändning',
        'network_traffic' => 'Nätverkstrafik',
        'uptime' => 'Upptid',
        'last_updated' => 'Senast uppdaterad',
        'no_node' => 'Ingen nod vald',
        'no_node_desc' => 'Välj en nod för att se övervakningsdata',
        'no_node_hint' => 'Använd rullgardinsmenyn ovan',
        'error' => 'Fel',
        'error_desc' => 'Det gick inte att ladda övervakningsdata',
        'error_fetch' => 'Det gick inte att hämta data från agenten',
        'error_node_gone' => 'Noden finns inte längre',
    ],

    'details' => [
        'heading' => 'Systemdetaljer',
        'button' => 'Detaljer',
        'close' => 'Nära',
        'no_data' => 'Inga data tillgängliga. Se till att noden är online.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Total användning',
        'cpu_cores' => 'Kärnor',
        'per_core' => 'Per kärnanvändning',

        'memory_section' => 'Minne',
        'total_memory' => 'Total',
        'used_memory' => 'Begagnad',
        'free_memory' => 'Gratis',
        'available_memory' => 'Tillgänglig',

        'swap_section' => 'Byta',
        'swap_none' => 'Inget byte konfigurerat på denna nod.',
        'swap_total' => 'Total',
        'swap_used' => 'Begagnad',
        'swap_free' => 'Gratis',
        'swap_usage' => 'Användande',

        'network_section' => 'Nätverk',
        'bytes_sent' => 'Bytes skickade',
        'bytes_recv' => 'Byte mottagna',
        'packets_sent' => 'Paket skickade',
        'packets_received' => 'Paket mottagna',

        'runtime_section' => 'Körning',
        'go_version' => 'Go version',
        'arch' => 'Arkitektur',
        'goroutines' => 'Goroutiner',
        'uptime' => 'Upptid',
    ],
    'servers' => [
        'heading' => 'Serveranvändning',
        'no_node' => 'Välj en nod för att se serveranvändning.',
        'no_servers' => 'Inga servrar hittades på denna nod.',
        'error_fetch' => 'Det gick inte att hämta serverdata från agenten.',
        'col' => [
            'name' => 'Server',
            'state' => 'Ange',
            'cpu' => 'CPU',
            'memory' => 'Minne',
            'disk' => 'Disk',
            'network' => 'Nätverk',
            'uptime' => 'Upptid',
        ],
        'states' => [
            'running' => 'Spring',
            'starting' => 'Startande',
            'stopping' => 'Stoppar',
            'offline' => 'Off-line',
            'crashed' => 'Kraschade',
            'unknown' => 'Okänd',
        ],
    ],
];
