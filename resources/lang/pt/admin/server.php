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

    'status' => [
        'online' => 'On-line',
        'offline' => 'Off-line',
        'starting' => 'Começando',
        'stopping' => 'Parando',
        'crashed' => 'Falha',
        'installing' => 'Instalando',
        'restoring_backup' => 'Restaurando Backup',
        'install_failed' => 'Falha na instalação',
        'reinstall_failed' => 'Falha na reinstalação',
        'suspended' => 'Suspenso',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Detalhes principais',
            'allocation' => 'Gerenciamento de Alocação',
            'feature_limits' => 'Limites de recursos do aplicativo',
            'resources' => 'Gerenciamento de recursos',
            'nest' => 'Configuração do ninho',
            'docker' => 'Configuração do Docker',
            'startup' => 'Configuração de inicialização',
            'variables' => 'Variáveis ​​de serviço',
        ],

        'fields' => [
            'name' => [
                'label' => 'Nome do servidor',
                'placeholder' => 'Nome do servidor',
                'helper' => 'Limites de caracteres: a-z A-Z 0-9 _ - . e espaços.',
            ],
            'owner' => [
                'label' => 'Proprietário do servidor',
                'helper' => 'Endereço de e-mail do proprietário do servidor.',
            ],
            'description' => [
                'label' => 'Descrição do servidor',
                'helper' => 'Uma breve descrição deste servidor.',
            ],
            'start_on_completion' => [
                'label' => 'Iniciar o servidor quando instalado',
            ],
            'node' => [
                'label' => 'Nó',
                'helper' => 'O nó no qual este servidor será implantado.',
            ],
            'allocation' => [
                'label' => 'Alocação padrão',
                'helper' => 'A alocação principal que será atribuída a este servidor.',
            ],
            'additional_allocations' => [
                'label' => 'Alocações Adicionais',
                'helper' => 'Alocações adicionais a serem atribuídas a este servidor na criação.',
            ],
            'database_limit' => [
                'label' => 'Limite de banco de dados',
                'helper' => 'O número total de bancos de dados que um usuário pode criar para este servidor.',
            ],
            'allocation_limit' => [
                'label' => 'Limite de Alocação',
                'helper' => 'O número total de alocações que um usuário pode criar para este servidor.',
            ],
            'backup_limit' => [
                'label' => 'Limite de backup',
                'helper' => 'O número total de backups que podem ser criados para este servidor.',
            ],
            'cpu' => [
                'label' => 'Limite de CPU',
                'helper' => 'Defina 0 para nenhum limite de CPU. Um núcleo virtual completo é 100%.',
            ],
            'threads' => [
                'label' => 'Fixação de CPU',
                'helper' => 'Avançado: use um único número ou uma lista separada por vírgulas, por exemplo 0, 0-1,3 ou 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memória',
                'helper' => 'A quantidade máxima de memória permitida para este contêiner. Defina 0 para ilimitado.',
            ],
            'swap' => [
                'label' => 'Trocar',
                'helper' => 'Defina 0 para desabilitar a troca ou -1 para permitir troca ilimitada.',
            ],
            'disk' => [
                'label' => 'Espaço em disco',
                'helper' => 'Defina 0 para permitir uso ilimitado do disco.',
            ],
            'io' => [
                'label' => 'Bloco IO Peso',
                'helper' => 'Avançado: desempenho de E/S em relação a outros contêineres em execução. O valor deve ser de 10 a 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Habilitar assassino OOM',
                'helper' => 'Encerra o servidor se ele violar os limites de memória.',
            ],
            'nest' => [
                'label' => 'Ninho',
                'helper' => 'Selecione o Nest em que este servidor será agrupado.',
            ],
            'egg' => [
                'label' => 'Ovo',
                'helper' => 'Selecione o Egg que definirá como este servidor deverá operar.',
            ],
            'skip_scripts' => [
                'label' => 'Pular script de instalação do Egg',
                'helper' => 'Se o Egg selecionado tiver um script de instalação anexado, o script será executado durante a instalação, a menos que esta opção esteja marcada.',
            ],
            'image' => [
                'label' => 'Imagem Docker',
                'helper' => 'Selecione uma imagem no menu suspenso ou insira uma imagem personalizada abaixo.',
            ],
            'custom_image' => [
                'label' => 'Imagem Docker personalizada',
                'placeholder' => 'Ou insira uma imagem personalizada...',
                'helper' => 'Esta é a imagem padrão do Docker que será usada para executar este servidor.',
            ],
            'startup' => [
                'label' => 'Comando de inicialização',
                'helper' => 'Substitutos disponíveis: {{SERVER_MEMORY}}, {{SERVER_IP}} e {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Selecione um ovo para configurar variáveis ​​de serviço',
            ],
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
            'label' => 'Nó',
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
            'label' => 'Ninho',
            'helper' => 'O Nest escolhido para esse servidor',
        ],
        'egg' => [
            'label' => 'Ovo',
            'helper' => 'O Egg que define o comportamento do servidor.',
        ],
        'startup' => [
            'label' => 'Comando de inicialização',
            'helper' => 'O comando de inicialização do servidor.',
        ],
        'image' => [
            'label' => 'Imagem Docker',
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
            'label' => 'Trocar',
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
            'helper' => 'Número máximo de backups.',
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
        'node' => 'Nó',
        'allocation' => 'Alocações',
        'status' => 'Status',
        'egg' => 'Ovo',
        'memory' => 'Memória',
        'disk' => 'Armazenamento',
        'cpu' => 'CPU',
        'created' => 'Criar',
        'updated' => 'Atualizar',
        'installed' => 'Instalado',
        'no_status' => 'Sem Status',
        'unlimited' => 'Ilimitado',
    ],

    'messages' => [
        'created' => 'O servidor foi criado com sucesso.',
        'updated' => 'O servidor foi atualizado com sucesso.',
        'deleted' => 'O servidor foi excluído com sucesso.',
    ],

    'actions' => [
        'edit' => 'Editar',
        'random' => 'Aleatório',
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
        'primary_allocation_updated' => 'Alocação primária atualizada.',
        'database_created' => 'Banco de dados criado.',
        'database_password_reset' => 'Redefinição de senha do banco de dados.',
        'database_deleted' => 'Banco de dados excluído.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Informação',
            'build_configuration' => 'Configuração de compilação',
            'startup' => 'Comece',
            'manage' => 'Gerenciar',
        ],

        'sections' => [
            'resource_management' => 'Gerenciamento de recursos',
            'application_feature_limits' => 'Limites de recursos do aplicativo',
            'allocation_management' => 'Gerenciamento de Alocação',
            'startup_command_modification' => 'Modificação do comando de inicialização',
            'service_configuration' => 'Configuração de serviço',
            'docker_image_configuration' => 'Configuração de imagem Docker',
            'service_variables' => 'Variáveis ​​de serviço',
            'reinstall_server' => 'Reinstale o servidor',
            'install_status' => 'Status de instalação',
            'suspend_server' => 'Suspender Servidor',
            'unsuspend_server' => 'Cancelar suspensão do servidor',
            'transfer_server' => 'Servidor de transferência',
            'delete_server' => 'Excluir servidor',
        ],

        'section_descriptions' => [
            'service_configuration' => 'A alteração desses valores pode acionar uma reinstalação. O servidor será parado imediatamente para essa operação.',
            'reinstall_server' => 'Isto irá reinstalar o servidor com os scripts de serviço atribuídos. Isso pode substituir os dados do servidor.',
            'install_status' => 'Altere o status da instalação de desinstalado para instalado ou vice-versa.',
            'suspend_server' => 'Isso interromperá a execução de processos e impedirá que o usuário gerencie o servidor por meio do painel ou API.',
            'unsuspend_server' => 'Isso cancelará a suspensão do servidor e restaurará o acesso normal do usuário.',
            'transfer_server_transferring' => 'Este servidor está sendo transferido para outro nó.',
            'transfer_server' => 'Transfira este servidor para outro nó conectado a este painel.',
            'delete_server' => 'Isso exclui permanentemente o servidor do painel e do Agente. A exclusão forçada ignora a exclusão do agente, se necessário.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Nome do servidor',
                'helper' => 'Limites de caracteres: a-zA-Z0-9_-, espaços e caracteres imprimíveis padrão.',
            ],
            'server_owner' => [
                'label' => 'Proprietário do servidor',
                'helper' => 'A alteração da propriedade revoga automaticamente os tokens daemon do proprietário anterior.',
            ],
            'server_description' => [
                'label' => 'Descrição do servidor',
                'helper' => 'Uma breve descrição deste servidor.',
            ],
            'server_uuid' => [
                'label' => 'UUID do servidor',
            ],
            'server_uuid_short' => [
                'label' => 'UUID do servidor (curto)',
            ],
            'external_identifier' => [
                'label' => 'Identificador Externo',
                'helper' => 'Deixe em branco para não atribuir um identificador externo. O ID externo deve ser exclusivo para este servidor.',
            ],
            'game_port' => [
                'label' => 'Porta de jogo',
                'helper' => 'O endereço de conexão padrão que será usado para este servidor de jogo.',
            ],
            'additional_ports' => [
                'label' => 'Portas Adicionais',
                'helper' => 'Atribua ou remova portas extras. Portas idênticas em IPs diferentes não podem ser atribuídas ao mesmo servidor.',
            ],
            'startup_command' => [
                'label' => 'Comando de inicialização',
                'helper' => 'Disponível por padrão: {{SERVER_MEMORY}}, {{SERVER_IP}} e {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Comando de inicialização padrão',
                'error' => 'ERRO: Inicialização não definida!',
            ],
            'cpu_limit' => [
                'label' => 'Limite de CPU',
                'helper' => 'Cada núcleo virtual é 100%. Defina 0 para tempo de CPU irrestrito.',
            ],
            'cpu_pinning' => [
                'label' => 'Fixação de CPU',
                'helper' => 'Avançado: deixe em branco para todos os núcleos. Exemplos: 0, 0-1,3 ou 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Memória Alocada',
                'helper' => 'A quantidade máxima de memória permitida para este contêiner. Defina 0 para ilimitado.',
            ],
            'allocated_swap' => [
                'label' => 'Troca Alocada',
                'helper' => 'Defina 0 para desabilitar a troca ou -1 para permitir troca ilimitada.',
            ],
            'disk_space_limit' => [
                'label' => 'Limite de espaço em disco',
                'helper' => 'Defina 0 para permitir uso ilimitado do disco.',
            ],
            'block_io_proportion' => [
                'label' => 'Proporção de IO do bloco',
                'helper' => 'Avançado: desempenho de E/S em relação a outros contêineres em execução. O valor deve ser de 10 a 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Desativar assassino OOM',
                'helper' => 'A ativação do OOM killer pode fazer com que os processos do servidor sejam encerrados inesperadamente.',
            ],
            'database_limit' => [
                'label' => 'Limite de banco de dados',
                'helper' => 'O número total de bancos de dados que um usuário pode criar para este servidor.',
            ],
            'allocation_limit' => [
                'label' => 'Limite de Alocação',
                'helper' => 'O número total de alocações que um usuário pode criar para este servidor.',
            ],
            'backup_limit' => [
                'label' => 'Limite de backup',
                'helper' => 'O número total de backups que podem ser criados para este servidor.',
            ],
            'image' => [
                'label' => 'Imagem',
                'helper' => 'Selecione uma imagem no menu suspenso ou insira uma imagem personalizada abaixo.',
            ],
            'custom_image' => [
                'label' => 'Imagem personalizada',
                'placeholder' => 'Ou insira uma imagem personalizada...',
                'helper' => 'Esta é a imagem Docker que será usada para executar este servidor.',
            ],
            'transfer_node' => [
                'label' => 'Nó',
                'helper' => 'O nó para o qual este servidor será transferido.',
            ],
            'transfer_allocation' => [
                'label' => 'Alocação padrão',
                'helper' => 'A alocação principal que será atribuída a este servidor.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Alocações Adicionais',
                'helper' => 'Alocações adicionais a serem atribuídas a este servidor na transferência.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstale o servidor',
            'toggle_install_status' => 'Alternar status de instalação',
            'suspend_server' => 'Suspender Servidor',
            'unsuspend_server' => 'Cancelar suspensão do servidor',
            'transfer_server' => 'Servidor de transferência',
            'confirm' => 'Confirmar',
            'delete_server' => 'Excluir servidor',
            'forcibly_delete_server' => 'Excluir servidor à força',
        ],
    ],

    'allocations' => [
        'title' => 'Alocações',

        'table' => [
            'ip' => 'PI',
            'port' => 'Porta',
            'alias' => 'Alias',
            'primary' => 'Primário',
            'notes' => 'Notas',
            'created' => 'Criado',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'Nenhum alias atribuído',
        ],

        'actions' => [
            'make_primary' => 'Tornar primário',
        ],
    ],

    'databases' => [
        'title' => 'Bancos de dados',

        'table' => [
            'database' => 'Banco de dados',
            'username' => 'Nome de usuário',
            'remote' => 'Remoto',
            'host' => 'Hospedar',
            'max_connections' => 'Máximo de conexões',
            'created' => 'Criado',
        ],

        'placeholder' => [
            'unlimited' => 'Ilimitado',
        ],

        'actions' => [
            'create_database' => 'Criar banco de dados',
            'reset_password' => 'Redefinir senha',
            'delete' => 'Excluir',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Nome do banco de dados',
                'helper' => 'O painel irá prefixar isso com o ID do servidor, correspondendo ao antigo painel de administração.',
            ],
            'database_host' => [
                'label' => 'Host de banco de dados',
            ],
            'remote' => [
                'label' => 'Remoto',
            ],
            'max_connections' => [
                'label' => 'Máximo de conexões',
            ],
        ],
    ],
];
