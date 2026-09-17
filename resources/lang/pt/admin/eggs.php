<?php

return [

    'tabs' => [
        'configuration' => 'Configuração do ovo',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'Configuração',
        ],
        'identity' => [
            'title' => 'Identidade',
        ],
        'docker_images' => [
            'title' => 'Imagens Docker',
            'description' => 'As imagens docker disponíveis para servidores que usam este ovo. Insira um por linha.',
        ],
        'process_management' => [
            'title' => 'Gestão de Processos',
        ],
        'variables' => [
            'title' => 'Variáveis',
        ],
        'install_script' => [
            'title' => 'Instalar script',
        ],
    ],

    'fields' => [
        'nest' => 'Ninho',
        'uuid' => 'UUID',
        'name' => 'Nome',
        'author' => 'Autor',
        'image' => 'Imagem',
        'description' => 'Descrição',
        'image_name' => 'Nome da imagem',
        'image_uri' => 'URI da imagem',
        'add_docker_image' => 'Adicionar imagem Docker',
        'force_outgoing_ip' => 'Forçar IP de saída',
        'features' => 'Características',
        'startup' => 'Comando de inicialização',
        'config_stop' => 'Comando de parada',
        'config_from' => 'Copiar configurações de',
        'config_startup' => 'Iniciar configuração (JSON)',
        'config_logs' => 'Configuração de log (JSON)',
        'config_files' => 'Arquivos de configuração (JSON)',
        'file_denylist' => 'Lista de bloqueio de arquivos',
        'env_variable' => 'Variável de ambiente',
        'user_viewable' => 'Os usuários podem visualizar',
        'user_editable' => 'Os usuários podem editar',
        'rules' => 'Regras de entrada',
        'default_value' => 'Valor padrão',
        'script_install' => 'Instalar script',
        'script_container' => 'Contêiner de script',
        'script_entry' => 'Comando de ponto de entrada de script',
        'copy_script_from' => 'Copiar script de',
        'script_is_privileged' => 'Privilegiado',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'Força todo o tráfego de rede de saída a ter seu IP de origem vinculado ao IP do IP de alocação primário do servidor.',
        'features' => 'Recursos adicionais pertencentes ao ovo. Útil para configurar modificações adicionais no painel.',
        'file_denylist' => 'Arquivos que não devem ser editados pelo usuário.',
        'script_is_privileged' => 'Execute o script de instalação como um contêiner privilegiado (root).',
    ],

    'actions' => [
        'export' => 'Exportar',
        'create' => 'Criar ovo',
        'edit' => 'Editar',
    ],

    'notices' => [
        'cannot_delete' => 'Não é possível excluir o ovo',
        'cannot_delete_body' => 'Este ovo possui servidores :count associados. Exclua ou reatribua-os primeiro.',
        'cannot_delete_multiple' => 'Não é possível excluir ovos com servidores',
        'cannot_delete_multiple_body' => 'O(s) ovo(s) :count possuem servidores associados e foram ignorados.',
    ],

];
