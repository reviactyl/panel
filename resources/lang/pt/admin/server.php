<?php

return [
    'label' => 'Servidor',
    'plural-label' => 'Servidores',

    'sections' => [
        'identity' => [
            'title' => 'Identidade',
            'description' => 'Informações básicas sobre o servidor e sua propriedade.',
        ],
        'allocation' => [
            'title' => 'Alocação',
            'description' => 'Selecione o node e a alocação de rede para este servidor.',
        ],
        'startup' => [
            'title' => 'Iniciar',
            'description' => 'Configure o egg, o comando de inicialização e a imagem Docker.',
        ],
        'resources' => [
            'title' => 'Limites de recursos',
            'description' => 'Defina os limites de recursos do servidor.',
        ],
        'feature_limits' => [
            'title' => 'Limites de Opcionais',
            'description' => 'Limitar bancos de dados, alocações e backups.',
        ],
        'environment' => [
            'title' => 'Variáveis ​​de ambiente',
            'description' => 'Defina os valores de ambiente para o ovo selecionado.',
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Modo Avançado',
            'helper' => 'Ative esta opção para exibir as configurações adicionais do servidor. Ative somente se você compreender as implicações das configurações adicionais.',
        ],
        'external_id' => [
            'label' => 'ID Externo',
            'helper' => 'Identificador único opcional para este servidor.',
        ],
        'owner' => [
            'label' => 'Dono',
            'helper' => 'Selecione o usuário que é o proprietário deste servidor.',
        ],
        'name' => [
            'label' => 'Nome',
            'placeholder' => 'Nome do Servidor',
            'helper' => 'Um nome curto para este servidor.',
        ],
        'description' => [
            'label' => 'Descrição',
            'placeholder' => 'Descrição do Servidor',
            'helper' => 'Descrição opcional para este servidor.',
        ],
        'node' => [
            'label' => 'Node',
            'helper' => 'O node no qual este servidor será implantado.',
        ],
        'allocation' => [
            'label' => 'Alocação Primária',
            'helper' => 'A alocação padrão de IP/porta para este servidor.',
        ],
        'additional_allocations' => [
            'label' => 'Alocações Adicionais',
            'helper' => 'Alocações extras opcionais para atribuir.',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'O Nest escolhido para esse servidor',
        ],
        'egg' => [
            'label' => 'Egg',
            'helper' => 'O Egg que define o comportamento do servidor.',
        ],
        'startup' => [
            'label' => 'Comando de inicialização',
            'helper' => 'O comando de inicialização do servidor.',
        ],
        'image' => [
            'label' => 'Imagem Docker',
            'placeholder' => 'e.g. ghcr.io/pterodactyl/yolks:java_17',
            'helper' => 'Imagem Docker usada para executar este servidor.',
            'custom' => 'Personalizado',
        ],
        'skip_scripts' => [
            'label' => 'Ignorar Script do Egg',
            'helper' => 'Ignorar os scripts de instalação do Egg durante a criação.',
        ],
        'start_on_completion' => [
            'label' => 'Começar na conclusão',
            'helper' => 'Iniciar o servidor automaticamente após a instalação.',
        ],
        'memory' => [
            'label' => 'Memória',
            'helper' => 'Alocação total de memória. Defina como 0 para ilimitada. (Memória ilimitada não funciona para ovos do Minecraft devido ao comando de inicialização)',
        ],
        'swap' => [
            'label' => 'Swap',
            'helper' => 'Alocação de memória de Swap. Defina como 0 para desativar a troca ou -1 para permitir troca ilimitada.',
        ],
        'disk' => [
            'label' => 'Armazenamento',
            'helper' => 'Alocação de armazenamento. Defina como 0 para ilimitado.',
        ],
        'io' => [
            'label' => 'Peso IO',
            'helper' => 'Prioridade relativa de E/S de disco (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'Limite da CPU em porcentagem. 100% significa um núcleo totalmente utilizado, 200% significa dois núcleos totalmente utilizados, etc.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Insira o tamanho em GiB',
            'helper' => 'Você pode inserir tamanhos em GiB usando o sufixo "GiB" (por exemplo, 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'Threads de CPU',
            'helper' => 'Fixação opcional da Thread. Exemplo: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Desativar o OOM Killer',
            'helper' => 'Impedir que o kernel encerre o processo quando houver falta de memória.',
        ],
        'database_limit' => [
            'label' => 'Limite de banco de dados',
            'helper' => 'Número máximo de bases de dados.',
        ],
        'allocation_limit' => [
            'label' => 'Limite de Alocação',
            'helper' => 'Número máximo de alocações.',
        ],
        'backup_limit' => [
            'label' => 'Limite de backup',
            'helper' => 'Maximum number of backups.',
        ],
        'environment' => [
            'key' => 'Variável',
            'value' => 'Valor',
            'helper' => 'Variáveis ​​de ambiente para este Egg.',
        ],
        'use_custom_image' => [
            'label' => 'Usar imagem personalizada',
            'helper' => 'Ative a opção para usar uma imagem Docker personalizada em vez de uma fornecida pelo pacote egg.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Nome',
        'owner' => 'Dono',
        'node' => 'Node',
        'allocation' => 'Alocações',
        'status' => 'Status',
        'egg' => 'Egg',
        'memory' => 'Memória',
        'disk' => 'Armazenamento',
        'cpu' => 'CPU',
        'created' => 'Criar',
        'updated' => 'Atualizar',
        'installed' => 'Instalado',
        'no_status' => 'Sem Status',
    ],

    'messages' => [
        'created' => 'O servidor foi criado com sucesso.',
        'updated' => 'O servidor foi atualizado com sucesso.',
        'deleted' => 'O servidor foi excluído com sucesso.',
    ],

    'actions' => [
        'edit' => 'Editar',
        'toggle_install_status' => 'Alternar status de instalação',
        'suspend' => 'Suspender',
        'unsuspend' => 'Cancelar suspensão',
        'suspended' => 'Suspenso',
        'unsuspended' => 'Não suspenso',
        'reinstall' => 'Reinstale',
        'delete' => 'Apagar',
        'delete_forcibly' => 'Apagar Forçado',
        'view' => 'Ver',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'Você está tentando excluir a alocação padrão para este servidor, mas não há alocação de fallback para usar.',
        'marked_as_failed' => 'Este servidor foi marcado como tendo falhado em uma instalação anterior. O status atual não pode ser alternado neste estado.',
        'bad_variable' => 'Houve um erro de validação com a variável :name.',
        'daemon_exception' => 'Houve uma exceção ao tentar se comunicar com o daemon resultando em um código de resposta HTTP/:code. Esta exceção foi registrada. (id da requisição: :request_id)',
        'default_allocation_not_found' => 'A alocação padrão solicitada não foi encontrada nas alocações deste servidor.',
    ],

    'alerts' => [
        'install_toggled' => 'O status de instalação do servidor foi alterado.',
        'server_suspended' => 'O servidor foi :action.',
        'server_reinstalled' => 'A reinstalação do servidor foi iniciada.',
        'server_deleted' => 'O servidor foi excluído.',
        'server_delete_failed' => 'Falha ao excluir o servidor.',
        'startup_changed' => 'A configuração de inicialização para este servidor foi atualizada. Se o ninho ou egg deste servidor foi alterado, uma reinstalação ocorrerá agora.',
        'server_created' => 'O servidor foi criado com sucesso no painel. Por favor, aguarde alguns minutos para que o daemon instale completamente este servidor.',
        'build_updated' => 'Os detalhes de build para este servidor foram atualizados. Algumas alterações podem requerer uma reinicialização para entrar em vigor.',
        'suspension_toggled' => 'O status de suspensão do servidor foi alterado para :status.',
        'rebuild_on_boot' => 'Este servidor foi marcado como requerendo uma reconstrução do Container Docker. Isso acontecerá na próxima vez que o servidor for iniciado.',
        'details_updated' => 'Os detalhes do servidor foram atualizados com sucesso.',
        'docker_image_updated' => 'A imagem Docker padrão para usar neste servidor foi alterada com sucesso. Uma reinicialização é necessária para aplicar esta alteração.',
        'node_required' => 'Você deve ter pelo menos um nó configurado antes de poder adicionar um servidor a este painel.',
        'transfer_nodes_required' => 'Você deve ter pelo menos dois nós configurados antes de poder transferir servidores.',
        'transfer_started' => 'A transferência do servidor foi iniciada.',
        'transfer_not_viable' => 'O nó selecionado não tem o espaço em disco ou memória disponível necessários para acomodar este servidor.',
    ],
];
