<?php

return [
    'title' => 'Usuário',
    'exceptions' => [
        'delete_self' => 'Você não pode excluir sua própria conta.',
        'user_has_servers' => 'Não é possível excluir um usuário com servidores ativos vinculados à sua conta. Por favor, exclua os servidores antes de continuar.',
    ],
    'notices' => [
        'account_created' => 'A conta foi criada com sucesso.',
        'account_updated' => 'A conta foi atualizada com sucesso.',
    ],
    'details' => [
        'account_details' => 'Detalhes da Conta',
        'external_id' => 'ID externo',
        'username' => 'Nome de Usuário',
        'email' => 'Endereço de E-mail',
        'first_name' => 'Primeiro Nome',
        'last_name' => 'Ultimo Nome',
        'language' => 'Linguagem',
        'geolocate' => 'Geolocalizar (Automático)',
        'password' => 'Senha',
        'password_confirmation' => 'Confirmar Senha',
        'root_admin' => 'Administrador Root',
        'root_admin_desc' => 'Este usuário terá acesso total a todos os servidores e configurações do sistema.',
        'privileges' => 'Privilégios',
        'admin_status' => 'Status do administrador',
    ],
];
