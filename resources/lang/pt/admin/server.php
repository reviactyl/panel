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
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Starting',
        'stopping' => 'Stopping',
        'crashed' => 'Crashed',
        'installing' => 'Installing',
        'restoring_backup' => 'Restoring Backup',
        'install_failed' => 'Install Failed',
        'reinstall_failed' => 'Reinstall Failed',
        'suspended' => 'Suspended',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Core Details',
            'allocation' => 'Allocation Management',
            'feature_limits' => 'Application Feature Limits',
            'resources' => 'Resource Management',
            'nest' => 'Nest Configuration',
            'docker' => 'Docker Configuration',
            'startup' => 'Startup Configuration',
            'variables' => 'Service Variables',
        ],

        'fields' => [
            'name' => [
                'label' => 'Server Name',
                'placeholder' => 'Server Name',
                'helper' => 'Character limits: a-z A-Z 0-9 _ - . and spaces.',
            ],
            'owner' => [
                'label' => 'Server Owner',
                'helper' => 'Email address of the Server Owner.',
            ],
            'description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'start_on_completion' => [
                'label' => 'Start Server when Installed',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be deployed to.',
            ],
            'allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on creation.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'cpu' => [
                'label' => 'CPU Limit',
                'helper' => 'Set 0 for no CPU limit. A full virtual core is 100%.',
            ],
            'threads' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: use a single number or comma separated list, for example 0, 0-1,3, or 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk' => [
                'label' => 'Disk Space',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Enable OOM Killer',
                'helper' => 'Terminates the server if it breaches memory limits.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Select the Nest that this server will be grouped under.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Select the Egg that will define how this server should operate.',
            ],
            'skip_scripts' => [
                'label' => 'Skip Egg Install Script',
                'helper' => 'If the selected Egg has an install script attached to it, the script will run during install unless this is checked.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Docker Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the default Docker image that will be used to run this server.',
            ],
            'startup' => [
                'label' => 'Startup Command',
                'helper' => 'Available substitutes: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Select an egg to configure service variables',
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
        'unlimited' => 'Unlimited',
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
        'primary_allocation_updated' => 'Primary allocation updated.',
        'database_created' => 'Database created.',
        'database_password_reset' => 'Database password reset.',
        'database_deleted' => 'Database deleted.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build Configuration',
            'startup' => 'Startup',
            'manage' => 'Manage',
        ],

        'sections' => [
            'resource_management' => 'Resource Management',
            'application_feature_limits' => 'Application Feature Limits',
            'allocation_management' => 'Allocation Management',
            'startup_command_modification' => 'Startup Command Modification',
            'service_configuration' => 'Service Configuration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Service Variables',
            'reinstall_server' => 'Reinstall Server',
            'install_status' => 'Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'delete_server' => 'Delete Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Changing these values can trigger a reinstall. The server will be stopped immediately for that operation.',
            'reinstall_server' => 'This will reinstall the server with the assigned service scripts. This could overwrite server data.',
            'install_status' => 'Change install status from uninstalled to installed, or vice versa.',
            'suspend_server' => 'This will stop running processes and block the user from managing the server through the panel or API.',
            'unsuspend_server' => 'This will unsuspend the server and restore normal user access.',
            'transfer_server_transferring' => 'This server is currently being transferred to another node.',
            'transfer_server' => 'Transfer this server to another node connected to this panel.',
            'delete_server' => 'This permanently deletes the server from the panel and Agent. Force delete skips Agent deletion if necessary.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Server Name',
                'helper' => 'Character limits: a-zA-Z0-9_-, spaces, and standard printable characters.',
            ],
            'server_owner' => [
                'label' => 'Server Owner',
                'helper' => 'Changing ownership automatically revokes daemon tokens for the previous owner.',
            ],
            'server_description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (Short)',
            ],
            'external_identifier' => [
                'label' => 'External Identifier',
                'helper' => 'Leave empty to not assign an external identifier. The external ID should be unique to this server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'The default connection address that will be used for this game server.',
            ],
            'additional_ports' => [
                'label' => 'Additional Ports',
                'helper' => 'Assign or remove extra ports. Identical ports on different IPs cannot be assigned to the same server.',
            ],
            'startup_command' => [
                'label' => 'Startup Command',
                'helper' => 'Available by default: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Default Startup Command',
                'error' => 'ERROR: Startup Not Defined!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Limit',
                'helper' => 'Each virtual core is 100%. Set 0 for unrestricted CPU time.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: leave blank for all cores. Examples: 0, 0-1,3, or 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Allocated Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'allocated_swap' => [
                'label' => 'Allocated Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Space Limit',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'block_io_proportion' => [
                'label' => 'Block IO Proportion',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Disable OOM Killer',
                'helper' => 'Enabling OOM killer may cause server processes to exit unexpectedly.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the Docker image that will be used to run this server.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be transferred to.',
            ],
            'transfer_allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstall Server',
            'toggle_install_status' => 'Toggle Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'confirm' => 'Confirm',
            'delete_server' => 'Delete Server',
            'forcibly_delete_server' => 'Forcibly Delete Server',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primary',
            'notes' => 'Notes',
            'created' => 'Created',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'No Alias Assigned',
        ],

        'actions' => [
            'make_primary' => 'Make Primary',
        ],
    ],

    'databases' => [
        'title' => 'Databases',

        'table' => [
            'database' => 'Database',
            'username' => 'Username',
            'remote' => 'Remote',
            'host' => 'Host',
            'max_connections' => 'Max Connections',
            'created' => 'Created',
        ],

        'placeholder' => [
            'unlimited' => 'Unlimited',
        ],

        'actions' => [
            'create_database' => 'Create Database',
            'reset_password' => 'Reset Password',
            'delete' => 'Delete',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Database Name',
                'helper' => 'The panel will prefix this with the server ID, matching the old admin panel.',
            ],
            'database_host' => [
                'label' => 'Database Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max Connections',
            ],
        ],
    ],
];
