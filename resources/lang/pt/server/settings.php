<?php

return [
    'title' => 'Configurações',
    'sftp' => [
        'title' => 'Detalhes do SFTP',
        'address' => 'Endereço do Servidor',
        'username' => 'Nome de Usuário',
        'password' => 'A senha do seu SFTP é a mesma que você usa para acessar este painel.',
        'button' => 'Abrir SFTP',
    ],
    'info' => [
        'title' => 'Informações de Debug',
        'node' => 'Node',
        'server' => 'ID do Servidor',
    ],
    'rename' => [
        'title' => 'Alterar Detalhes do Servidor',
        'name' => 'Nome do Servidor',
        'description' => 'Descrição do Servidor',
        'button' => 'Salvar',
    ],
    'reinstall' => [
        'title' => 'Reinstalar Servidor',
        'confirm-title' => 'Confirmar reinstalação do servidor',
        'confirm' => 'Sim, reinstalar servidor',
        'info' => 'Seu servidor será desligado e alguns arquivos podem ser deletados ou modificados durante este processo. Tem certeza de que deseja continuar?',
        'info-1' => 'Reinstalar seu servidor irá desligá-lo e então executar novamente o script de instalação que o configurou inicialmente.',
        'info-2' => 'Alguns arquivos podem ser deletados ou modificados durante este processo. Por favor, faça backup dos seus dados antes de continuar.',
        'button' => 'Reinstalar Servidor',
    ],
];
