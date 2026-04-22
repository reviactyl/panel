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
        'refresh' => 'Refresh Data',
    ],

    'selector' => [
        'label' => 'Select Node',
        'placeholder' => 'Select a node...',
    ],

    'stats' => [
        'cpu_usage' => 'CPU Usage',
        'cpu_cores' => ':count cores available',
        'memory_usage' => 'Memory Usage',
        'disk_usage' => 'Disk Usage',
        'network_traffic' => 'Network Traffic',
        'uptime' => 'Uptime',
        'goroutines' => ':count goroutines',
        'last_updated' => 'Last Updated',
        'no_node' => 'No Node Selected',
        'no_node_desc' => 'Please select a node to view monitoring data',
        'no_node_hint' => 'Use the dropdown above',
        'error' => 'Error',
        'error_desc' => 'Unable to load monitoring data',
        'error_fetch' => 'Unable to fetch data from Wings',
        'error_node_gone' => 'Node no longer exists',
    ],

    'details' => [
        'heading' => 'System Details',
        'button' => 'Details',
        'close' => 'Close',
        'no_data' => 'No data available. Ensure the node is online.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Total Usage',
        'cpu_cores' => 'Cores',
        'per_core' => 'Per-Core Usage',

        'memory_section' => 'Memory',
        'total_memory' => 'Total',
        'used_memory' => 'Used',
        'free_memory' => 'Free',
        'available_memory' => 'Available',

        'swap_section' => 'Swap',
        'swap_none' => 'No swap configured on this node.',
        'swap_total' => 'Total',
        'swap_used' => 'Used',
        'swap_free' => 'Free',
        'swap_usage' => 'Usage',

        'network_section' => 'Network',
        'bytes_sent' => 'Bytes Sent',
        'bytes_recv' => 'Bytes Received',
        'packets_sent' => 'Packets Sent',
        'packets_received' => 'Packets Received',

        'runtime_section' => 'Runtime',
        'go_version' => 'Go Version',
        'arch' => 'Architecture',
        'goroutines' => 'Goroutines',
        'uptime' => 'Uptime',
    ],
    'servers' => [
        'heading'     => 'Server Usage',
        'no_node'     => 'Select a node to view server usage.',
        'no_servers'  => 'No servers found on this node.',
        'error_fetch' => 'Unable to fetch server data from Wings.',
        'col' => [
            'name'    => 'Server',
            'state'   => 'State',
            'cpu'     => 'CPU',
            'memory'  => 'Memory',
            'disk'    => 'Disk',
            'network' => 'Network',
            'uptime'  => 'Uptime',
        ],
        'states' => [
            'running'  => 'Running',
            'starting' => 'Starting',
            'stopping' => 'Stopping',
            'offline'  => 'Offline',
            'crashed'  => 'Crashed',
            'unknown'  => 'Unknown',
        ],
    ],
];
