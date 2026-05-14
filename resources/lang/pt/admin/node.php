<?php

return [
    'label' => ' Node',
    'plural-label' => 'Nós',

    'sections' => [
        'overview' => [
            'title' => 'Visão geral',
            'information-label' => 'Informações do nó',
            'version-label' => 'Versão do Agente',
            'architecture-label' => 'Arquitetura',
            'kernel-label' => 'Núcleo',
            'cpus-label' => 'Threads de CPU',
            'cpu-usage-label' => 'Uso da CPU',
            'memory-usage-label' => 'Uso de memória',
            'disk-usage-label' => 'Uso de disco',
        ],
        'tabs' => [
            'title' => 'Configuração do nó',
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
            'title' => 'Configuração',
            'config_description' => 'Arquivo de configuração',
            'deploy_description' => 'Gere um comando de implantação personalizado que pode ser usado para configurar o Agente no servidor de destino.',
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
            'placeholder' => 'nó.exemplo.com',
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
        'health' => 'Saúde',
        'health_http_status' => 'HTTP :status',
        'health_check_console' => 'verifique o console do navegador',
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
        'public' => 'Público',
        'maintenance' => 'Manutenção',
        'public_true' => 'Público',
        'public_false' => 'Privado',
        'maintenance_true' => 'Em manutenção',
        'maintenance_false' => 'Ativo',
    ],

    'actions' => [
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Apagar',
        'view' => 'Ver',
        'random' => 'Aleatório',
        'view_monitoring' => 'Ver monitoramento',
    ],

    'deployment' => [
        'generate_label' => 'Gerar token de implantação',
        'modal_heading' => 'Comando de implantação automática',
        'modal_description' => 'Execute este comando em seu nó para configurar automaticamente o Agente.',
        'modal_close' => 'Fechar',
        'command_label' => 'Comando de implantação',
        'command_helper' => 'Copie e execute este comando em seu servidor de nó.',
        'token_success' => 'Token gerado com sucesso',
        'token_success_body' => 'Copie e execute o comando abaixo em seu nó.',
        'save_first' => 'Salve o nó primeiro.',
        'auto_generated_key' => 'Chave de implantação de nó gerada automaticamente.',
        'error' => 'Erro ao gerar token. Por favor, tente novamente.',
    ],

    'general' => [
        'na' => 'N / D',
        'unavailable' => 'Indisponível',
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
            'ip' => 'PI',
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
                'label' => 'Alias ​​de IP',
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
