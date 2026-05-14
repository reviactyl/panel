<?php

return [
    'navigation' => [
        'label' => 'Monitoramento',
        'group' => 'Adiminstração',
    ],

    'page' => [
        'title' => 'Monitoramento',
        'heading' => 'Monitoramento Ao Vivo',
    ],

    'actions' => [
        'refresh' => 'Atualizar Dados',
    ],

    'selector' => [
        'label' => 'Selecionar Node',
        'placeholder' => 'Selecione o node',
    ],

    'stats' => [
        'cpu_usage' => 'Uso de CPI',
        'cpu_cores' => ':count núcleos disponíveis',
        'memory_usage' => 'Uso de Memória',
        'disk_usage' => 'Uso de Armazenamento',
        'network_traffic' => 'Trafego de Rede',
        'uptime' => 'Tempo de atividade',
        'last_updated' => 'Última atualização',
        'no_node' => 'Não Há Node Selecionado',
        'no_node_desc' => 'Por favor, selecione um node para visualizar os dados de monitoramento.',
        'no_node_hint' => 'Use o menu acima.',
        'error' => 'Erro',
        'error_desc' => 'Não foi possível carregar os dados de monitoramento.',
        'error_fetch' => 'Não foi possível buscar dados do agente',
        'error_node_gone' => 'O node não existe mais.',
    ],

    'details' => [
        'heading' => 'Detalhes do Sistema',
        'button' => 'Detalhes',
        'close' => 'Fechar',
        'no_data' => 'Não há dados disponíveis. Verifique se o node está online.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'Uso Total',
        'cpu_cores' => 'Núcleos',
        'per_core' => 'Utilização Por Core',

        'memory_section' => 'Memória',
        'total_memory' => 'Total',
        'used_memory' => 'Usado',
        'free_memory' => 'Livre',
        'available_memory' => 'Disponível',

        'swap_section' => 'Trocar',
        'swap_none' => 'Não há swap configurado para esse node',
        'swap_total' => 'Total',
        'swap_used' => 'Usado',
        'swap_free' => 'Livre',
        'swap_usage' => 'Uso',

        'network_section' => 'Rede',
        'bytes_sent' => 'Bytes Enviados',
        'bytes_recv' => 'Bytes Recepidos',
        'packets_sent' => 'Pacotes Enviados',
        'packets_received' => 'Pacotes Recebidos',

        'runtime_section' => 'Tempo de execução',
        'go_version' => 'Versão Ir',
        'arch' => 'Arquitetura',
        'goroutines' => 'Goroutines',
        'uptime' => 'Tempo de atividade',
    ],
    'servers' => [
        'heading' => 'Uso Do Servidor',
        'no_node' => 'Selecione um node para visualizar a utilização do servidor.',
        'no_servers' => 'Nenhum servidor encontrado neste node.',
        'error_fetch' => 'Não foi possível buscar dados do servidor do Agente.',
        'col' => [
            'name' => 'Servidor',
            'state' => 'Estado',
            'cpu' => 'CPU',
            'memory' => 'Memória',
            'disk' => 'Disco',
            'network' => 'Rede',
            'uptime' => 'Tempo de atividade',
        ],
        'states' => [
            'running' => 'Correndo',
            'starting' => 'Começando',
            'stopping' => 'Parando',
            'offline' => 'Off-line',
            'crashed' => 'Falha',
            'unknown' => 'Desconhecido',
        ],
    ],
];
