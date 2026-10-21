<?php

return [
    'label' => 'Cargo',
    'plural-label' => 'Cargos',
    'all' => 'Todos',
    'select_all' => 'Selecionar tudo',
    'deselect_all' => 'Desmarcar tudo',
    'details' => [
        'title' => 'Detalhes do cargo',
        'description' => 'Os cargos definem o que um usuário pode fazer no painel, na área administrativa e em servidores que não são dele.',
        'name' => 'Nome',
        'name_placeholder' => 'Equipe de suporte',
        'description_label' => 'Descrição',
        'description_placeholder' => 'Para que serve este cargo.',
    ],
    'table' => [
        'name' => 'Nome',
        'permissions' => 'Permissões',
        'users' => 'Usuários',
        'updated' => 'Última atualização',
    ],
    'groups' => [
        'management' => [
            'title' => 'Gerenciamento',
            'description' => 'Os servidores, as pessoas que os usam e a infraestrutura em que eles rodam.',
        ],
        'service' => [
            'title' => 'Gestão de Serviços',
            'description' => 'O que pode ser instalado em um servidor e como o próprio painel é estendido e atualizado.',
        ],
        'administration' => [
            'title' => 'Administração',
            'description' => 'Configuração, aparência e informações de todo o painel.',
        ],
    ],
    'resources' => [
        'servers' => 'Servidores',
        'users' => 'Usuários',
        'roles' => 'Cargos',
        'nodes' => 'Nós',
        'locations' => 'Locais',
        'database_hosts' => 'Hosts de banco de dados',
        'nests' => 'Ninhos',
        'eggs' => 'Eggs',
        'mounts' => 'Montagens',
        'alerts' => 'Alertas',
        'webhooks' => 'Webhooks',
        'extensions' => 'Extensões',
        'activity_log' => 'Logs',
        'monitoring' => 'Monitoramento',
        'settings' => 'Configurações',
        'designify' => 'Designify',
        'updates' => 'Atualizações de software',
    ],
    'actions' => [
        'send_email' => 'Enviar e-mail',
        'read' => 'Ver',
        'create' => 'Criar',
        'update' => 'Editar',
        'delete' => 'Excluir',
        'assign' => 'Atribuir a usuários',
        'manage' => 'Gerenciar',
        'install' => 'Instalar atualizações',
    ],
    'messages' => [
        'in_use' => 'Este cargo ainda está atribuído a usuários. Remova-o deles antes de excluí-lo.',
    ],
    'user' => [
        'roles' => 'Cargos',
        'roles_self' => 'Você não pode alterar seus próprios cargos.',
        'none' => 'Nenhum',
    ],
];
