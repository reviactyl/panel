<?php

return [
    'navigation' => [
        'label' => 'Escucha',
        'group' => 'Administración',
    ],

    'page' => [
        'title' => 'Escucha',
        'heading' => 'Monitoreo en vivo',
    ],

    'actions' => [
        'refresh' => 'Actualizar datos',
    ],

    'selector' => [
        'label' => 'Seleccionar nodo',
        'placeholder' => 'Seleccione un nodo...',
    ],

    'stats' => [
        'cpu_usage' => 'Uso de CPU',
        'cpu_cores' => 'Núcleos :count disponibles',
        'memory_usage' => 'Uso de la memoria',
        'disk_usage' => 'Uso del disco',
        'network_traffic' => 'Tráfico de red',
        'uptime' => 'tiempo de actividad',
        'goroutines' => 'Gorrutinas :count',
        'last_updated' => 'Última actualización',
        'no_node' => 'Ningún nodo seleccionado',
        'no_node_desc' => 'Seleccione un nodo para ver los datos de monitoreo',
        'no_node_hint' => 'Utilice el menú desplegable de arriba',
        'error' => 'Error',
        'error_desc' => 'No se pueden cargar datos de monitoreo',
        'error_fetch' => 'No se pueden recuperar datos del Agente',
        'error_node_gone' => 'El nodo ya no existe',
    ],

    'details' => [
        'heading' => 'Detalles del sistema',
        'button' => 'Detalles',
        'close' => 'Cerca',
        'no_data' => 'No hay datos disponibles. Asegúrese de que el nodo esté en línea.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Uso total',
        'cpu_cores' => 'Núcleos',
        'per_core' => 'Uso por núcleo',

        'memory_section' => 'Memoria',
        'total_memory' => 'Total',
        'used_memory' => 'Usado',
        'free_memory' => 'Gratis',
        'available_memory' => 'Disponible',

        'swap_section' => 'Intercambio',
        'swap_none' => 'No hay intercambio configurado en este nodo.',
        'swap_total' => 'Total',
        'swap_used' => 'Usado',
        'swap_free' => 'Gratis',
        'swap_usage' => 'Uso',

        'network_section' => 'Red',
        'bytes_sent' => 'Bytes enviados',
        'bytes_recv' => 'Bytes recibidos',
        'packets_sent' => 'Paquetes enviados',
        'packets_received' => 'Paquetes recibidos',

        'runtime_section' => 'Tiempo de ejecución',
        'go_version' => 'Ir a la versión',
        'arch' => 'Arquitectura',
        'goroutines' => 'Gorrutinas',
        'uptime' => 'tiempo de actividad',
    ],
    'servers' => [
        'heading' => 'Uso del servidor',
        'no_node' => 'Seleccione un nodo para ver el uso del servidor.',
        'no_servers' => 'No se encontraron servidores en este nodo.',
        'error_fetch' => 'No se pueden recuperar los datos del servidor del Agente.',
        'col' => [
            'name' => 'Servidor',
            'state' => 'Estado',
            'cpu' => 'CPU',
            'memory' => 'Memoria',
            'disk' => 'Disco',
            'network' => 'Red',
            'uptime' => 'tiempo de actividad',
        ],
        'states' => [
            'running' => 'Correr',
            'starting' => 'A partir de',
            'stopping' => 'Parada',
            'offline' => 'Desconectado',
            'crashed' => 'Se estrelló',
            'unknown' => 'Desconocido',
        ],
    ],
];
