<?php

return [
    'daemon_connection_failed' => 'Houve uma exceção ao tentar se comunicar com o daemon resultando em um código de resposta HTTP/:code. Esta exceção foi registrada.',
    'node' => [
        'servers_attached' => 'Um nó não deve ter nenhum servidor vinculado a ele para ser excluído.',
        'daemon_off_config_updated' => 'A configuração do daemon foi atualizada, mas ocorreu um erro ao tentar atualizar automaticamente o arquivo de configuração no Daemon. Você precisará atualizar manualmente o arquivo de configuração (config.yml) para que o daemon aplique essas alterações.',
    ],
    'allocations' => [
        'server_using' => 'Um servidor está atualmente atribuído a esta alocação. Uma alocação só pode ser excluída se nenhum servidor estiver atualmente atribuído.',
        'too_many_ports' => 'Adicionar mais de 1000 portas em um único intervalo de uma vez não é suportado.',
        'invalid_mapping' => 'O mapeamento fornecido para :port era inválido e não pôde ser processado.',
        'cidr_out_of_range' => 'A notação CIDR só permite máscaras entre /25 e /32.',
        'port_out_of_range' => 'As portas em uma alocação devem ser maiores que 1024 e menores ou iguais a 65535.',
    ],
    'nest' => [
        'delete_has_servers' => 'Um Ninho com servidores ativos anexados não pode ser excluído do Painel.',
        'egg' => [
            'delete_has_servers' => 'Um Egg com servidores ativos anexados não pode ser excluído do Painel.',
            'invalid_copy_id' => 'O Egg selecionado para copiar um script não existe, ou está copiando um script ele mesmo.',
            'must_be_child' => 'A diretiva "Copiar Configurações De" para este Egg deve ser uma opção filha do Ninho selecionado.',
            'has_children' => 'Este Egg é pai de um ou mais outros Eggs. Por favor, exclua esses Eggs antes de excluir este Egg.',
        ],
        'variables' => [
            'env_not_unique' => 'A variável de ambiente :name deve ser única para este Egg.',
            'reserved_name' => 'A variável de ambiente :name é protegida e não pode ser atribuída a uma variável.',
            'bad_validation_rule' => 'A regra de validação ":rule" não é uma regra válida para esta aplicação.',
        ],
        'importer' => [
            'json_error' => 'Houve um erro ao tentar analisar o arquivo JSON: :error.',
            'file_error' => 'O arquivo JSON fornecido não era válido.',
            'invalid_json_provided' => 'O arquivo JSON fornecido não está em um formato que possa ser reconhecido.',
        ],
    ],
    'subusers' => [
        'editing_self' => 'Editar sua própria conta de subusuário não é permitido.',
        'user_is_owner' => 'Você não pode adicionar o proprietário do servidor como subusuário para este servidor.',
        'subuser_exists' => 'Um usuário com esse endereço de e-mail já está atribuído como subusuário para este servidor.',
    ],
    'subuser_preview' => [
        'start_blocked' => 'Não é possível iniciar outra visualização enquanto o modo de visualização estiver ativo.',
        'owner_only' => 'Somente o proprietário do servidor pode visualizar um subusuário.',
        'session_unavailable' => 'Esta sessão de pré-visualização não está mais disponível.',
        'session_expired' => 'A validade desta sessão de visualização expirou.',
        'concurrent_start' => 'Já está começando uma sessão de pré-visualização.',
        'account_unavailable' => 'As informações da conta não estão disponíveis durante a visualização do subusuário.',
        'categories_unavailable' => 'As categorias de servidores pessoais não estão disponíveis durante a visualização do subusuário.',
        'permission_denied' => 'Você não tem permissão para realizar essa ação na visualização.',
        'resource_unavailable' => 'Este recurso não está disponível durante a visualização do subusuário.',
        'live_connection_unavailable' => 'Essa conexão em tempo real não está disponível durante a visualização do subusuário.',
        'file_not_found' => 'O arquivo solicitado não existe nesta visualização.',
        'file_too_large' => 'Este arquivo é grande demais para ser exibido na visualização.',
        'state_too_large' => 'Esta pré-visualização atingiu seu limite de armazenamento.',
        'unsafe_pull_url' => 'Somente URLs HTTPS seguras podem ser inseridas na visualização.',
        'action_unavailable' => 'Essa ação não está disponível durante a visualização do subusuário.',
        'database_limit' => 'Este servidor atingiu o limite do banco de dados.',
        'database_host_unavailable' => 'Não há nenhum host de banco de dados disponível para este servidor.',
        'task_limit' => 'Esta programação atingiu seu limite de tarefas.',
        'allocation_limit' => 'Este servidor atingiu seu limite de alocação.',
        'allocation_unavailable' => 'Não há alocação adicional disponível para este servidor.',
        'primary_allocation' => 'A alocação primária não pode ser removida.',
        'backup_limit' => 'Este servidor atingiu seu limite de backup.',
        'locked_backup' => 'Um backup bloqueado não pode ser excluído.',
        'variable_unavailable' => 'A variável de ambiente não está disponível ou é somente de leitura.',
        'docker_image_unavailable' => 'A imagem do Docker selecionada não está disponível para este servidor.',
    ],
    'databases' => [
        'delete_has_databases' => 'Não é possível excluir um servidor host de banco de dados que possui bancos de dados ativos vinculados.',
    ],
    'tasks' => [
        'chain_interval_too_long' => 'O tempo máximo de intervalo para uma tarefa encadeada é de 15 minutos.',
    ],
    'locations' => [
        'has_nodes' => 'Não é possível excluir uma localização que possui nós ativos anexados.',
    ],
    'users' => [
        'node_revocation_failed' => 'Falha ao revogar chaves no <a href=":link">Nó #:node</a>. :error',
    ],
    'deployment' => [
        'no_viable_nodes' => 'Nenhum nó que satisfaça os requisitos especificados para implantação automática pôde ser encontrado.',
        'no_viable_allocations' => 'Nenhuma alocação que satisfaça os requisitos para implantação automática foi encontrada.',
    ],
    'api' => [
        'resource_not_found' => 'O recurso solicitado não existe neste servidor.',
    ],
    'social' => [
        'unlink_only_login' => 'Não é possível desvincular seu único método de login sem definir uma senha primeiro.',
    ],
];
