<?php

return [
    'label' => ' Node',
    'plural-label' => 'Nodes',

    'sections' => [
        'overview' => [
            'title' => 'Overview',
            'information-label' => 'Node Information',
            'version-label' => 'Agent Version',
            'architecture-label' => 'Architecture',
            'kernel-label' => 'Kernel',
            'cpus-label' => 'CPU Threads',
            'cpu-usage-label' => 'CPU Usage',
            'memory-usage-label' => 'Memory Usage',
            'disk-usage-label' => 'Disk Usage',
        ],
        'tabs' => [
            'title' => 'Node Configuration',
        ],
        'identity' => [
            'title' => 'Identidade',
            'description' => 'Informações básicas sobre o node',
        ],
        'connection' => [
            'title' => 'Detalhes de Conexão',
            'description' => 'Configure a forma de conexão com este node.',
        ],
        'resources' => [
            'title' => 'Alocação de Recursos',
            'description' => 'Defina os limites de memória e disco para este node.',
        ],
        'daemon' => [
            'title' => 'Configurações do Daemon',
            'description' => 'Configure as definições específicas do daemon.',
        ],
        'configuration' => [
            'title' => 'Configuration',
            'config_description' => 'Configuration File',
            'deploy_description' => 'Generate a custom deployment command that can be used to configure Agent on the target server.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'Público',
            'helper' => 'Ao definir um node como privado, você estará negando a capacidade de implantação automática nesse nó.',
        ],
        'name' => [
            'label' => 'Nome',
            'placeholder' => 'Nome do Node',
            'helper' => 'Um nome descritivo para este node.',
        ],
        'description' => [
            'label' => 'Descrição',
            'placeholder' => 'Descrição do Node',
            'helper' => 'Descrição opcional para este node.',
        ],
        'location' => [
            'label' => 'Localização',
            'helper' => 'A localização à qual este node está atribuído.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'Nome de domínio totalmente qualificado ou endereço IP.',
        ],
        'ssl' => [
            'label' => 'Usar SSL',
            'helper' => 'Indica se o daemon neste node está configurado para usar SSL para comunicação segura.',
            'helper_forced' => 'Este painel está sendo executado em HTTPS, portanto o SSL é obrigatório para este node.',
        ],
        'behind_proxy' => [
            'label' => 'Atrás do proxy',
            'helper' => 'Habilite se este node estiver atrás de um proxy como o Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'Modo de Manutenção',
            'helper' => 'Impeça a criação de novos servidores neste node.',
        ],
        'memory' => [
            'label' => 'Memória Total',
            'helper' => 'Memória total disponível neste node, em MiB.',
        ],
        'memory_overallocate' => [
            'label' => 'Superalocação de memória',
            'helper' => 'Percentagem de memória a ser sobrealocada. Use -1 para desativar a verificação.',
        ],
        'disk' => [
            'label' => 'Total de Armazenamento',
            'helper' => 'Espaço total de armazenamento disponível neste nó, em MiB.',
        ],
        'disk_overallocate' => [
            'label' => 'Superalocação de Armazenamento',
            'helper' => 'Percentagem de armazenamento a ser sobrealocado. Use -1 para desativar a verificação.',
        ],
        'upload_size' => [
            'label' => 'Tamanho máximo de upload',
            'helper' => 'Tamanho máximo de arquivo permitido para upload através do painel web.',
        ],
        'daemon_base' => [
            'label' => 'Diretório Base',
            'placeholder' => '/home/daemon-files',
            'helper' => 'Diretório onde os arquivos do servidor são armazenados.',
        ],
        'daemon_listen' => [
            'label' => 'Porta Daemon',
            'helper' => 'A porta que o daemon utiliza para escutar comunicações HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'Porta SFTP',
            'helper' => 'A porta utilizada para conexões SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'ID do token',
        ],
        'container_text' => [
            'label' => 'Prefixo do contêiner',
            'helper' => 'Prefixo de texto exibido nos nomes dos contêineres.',
        ],
    ],

    'table' => [
        'health' => 'Health',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'check browser console',
        'id' => 'ID',
        'uuid' => 'UUID',
        'name' => 'Nome',
        'location' => 'Localização',
        'fqdn' => 'FQDN',
        'scheme' => 'Protocolo',
        'public' => 'Público',
        'behind_proxy' => 'Atrás do proxy',
        'maintenance_mode' => 'Manutenção',
        'memory' => 'Memória',
        'memory_overallocate' => 'Memória Cheia',
        'disk' => 'Armazenamento',
        'disk_overallocate' => 'Armazenamento Cheio',
        'upload_size' => 'Tamanho do upload',
        'daemon_listen' => 'Porta Daemon',
        'daemon_sftp' => 'Porta SFTP',
        'daemon_base' => 'Diretório Base',
        'servers' => 'Servidores',
        'created' => 'Criar',
        'updated' => 'Atualizar',
    ],

    'filters' => [
        'public' => 'Public',
        'maintenance' => 'Maintenance',
        'public_true' => 'Public',
        'public_false' => 'Private',
        'maintenance_true' => 'Under Maintenance',
        'maintenance_false' => 'Active',
    ],

    'actions' => [
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Apagar',
        'view' => 'Ver',
        'random' => 'Random',
        'view_monitoring' => 'View Monitoring',
    ],

    'deployment' => [
        'generate_label' => 'Generate Deployment Token',
        'modal_heading' => 'Auto-Deploy Command',
        'modal_description' => 'Run this command on your node to automatically configure Agent.',
        'modal_close' => 'Close',
        'command_label' => 'Deployment Command',
        'command_helper' => 'Copy and run this command on your node server.',
        'token_success' => 'Token Generated Successfully',
        'token_success_body' => 'Copy and run the command below on your node.',
        'save_first' => 'Please save the node first.',
        'auto_generated_key' => 'Automatically generated node deployment key.',
        'error' => 'Error generating token. Please try again.',
    ],

    'general' => [
        'na' => 'N/A',
        'unavailable' => 'Unavailable',
    ],

    'messages' => [
        'created' => 'O node foi criado com sucesso.',
        'updated' => 'O node foi atualizado com sucesso.',
        'deleted' => 'O node foi excluído com sucesso.',
        'cannot_delete_with_servers' => 'Não é possível excluir um node com servidores ativos.',
    ],

    'allocations' => [
        'label' => 'Alocações',
        'table' => [
            'ip' => 'IP',
            'port' => 'Porta',
            'alias' => 'Alias',
            'server' => 'Servidores',
            'notes' => 'Anotações',
            'created' => 'Criar',
            'unassigned' => 'Não atribuído',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'Endereço IP',
                'helper' => 'Suporta um único endereço IP ou CIDR (por exemplo, 192.0.2.1 ou 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'Portas',
                'helper' => 'Insira as portas ou intervalos (por exemplo, 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'IP Alias',
                'helper' => 'Alias ​​opcional para exibir em vez do endereço IP.',
            ],
        ],
        'actions' => [
            'add' => 'Adicionar alocação',
            'delete' => 'Apagar',
        ],
        'messages' => [
            'created' => 'Alocações adicionadas.',
            'deleted' => 'Alocação excluída.',
            'failed' => 'A ação de alocação falhou.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'O FQDN ou endereço IP fornecido não resolve para um endereço IP válido.',
        'fqdn_required_for_ssl' => 'Um nome de domínio totalmente qualificado que resolve para um endereço IP público é necessário para usar SSL neste nó.',
    ],
    'notices' => [
        'allocations_added' => 'As alocações foram adicionadas com sucesso a este nó.',
        'node_deleted' => 'O nó foi removido com sucesso do painel.',
        'location_required' => 'Você deve ter pelo menos uma localização configurada antes de poder adicionar um nó a este painel.',
        'node_created' => 'Novo Node criado com sucesso. Você pode configurar automaticamente o daemon nesta máquina acessando a guia “Configuração”. Antes de adicionar qualquer servidor, você deve primeiro alocar pelo menos um endereço IP e uma porta.',
        'node_updated' => 'As informações do nó foram atualizadas. Se quaisquer configurações do daemon foram alteradas, você precisará reiniciá-lo para que essas alterações entrem em vigor.',
        'unallocated_deleted' => 'Todas as portas não alocadas para <code>:ip</code> foram excluídas.',
    ],
];
