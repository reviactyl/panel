<?php

return [
    'username-required' => 'É necessário informar um nome de usuário ou e-mail.',
    'password-required' => 'Por favor, insira a senha da sua conta.',
    'email-required' => 'Um endereço de e-mail válido deve ser informado para continuar.',

    'login-title' => 'Entrar para Continuar',

    'username-label' => 'Nome de Usuário ou E-mail',
    'password-label' => 'Senha',

    'login-button' => 'Entrar',
    'return' => 'Voltar para o Login',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'Esta conta não foi vinculada a nenhuma conta :provider. Faça login primeiro com seu e-mail e senha e, em seguida, vincule sua conta :provider na página Configurações da conta.',
    ],

    'forgot-password' => [
        'title' => 'Solicitar Redefinição de Senha',
        'label' => 'Esqueceu a Senha?',
        'email-label' => 'E-mail',
        'email-content' => 'Digite o e-mail da sua conta para receber instruções sobre como redefinir sua senha.',
        'send-email' => 'Enviar E-mail',
    ],

    'checkpoint' => [
        'title' => 'Verificação de Dispositivo',
        'recovery-code' => 'Código de Recuperação',
        'auth-code' => 'Código de Autenticação',
        'is-missing' => 'Digite um dos códigos de recuperação gerados quando você configurou a autenticação em 2 fatores nesta conta para continuar.',
        'is-not-missing' => 'Digite o token de autenticação em dois fatores gerado pelo seu dispositivo.',
        'button' => 'Continuar',
        'lost-device' => 'Perdi Meu Dispositivo',
        'not-lost-device' => 'Ainda Tenho Meu Dispositivo',

    ],

    'reset-password' => [
        'new-required' => 'Uma nova senha é obrigatória.',
        'min-required' => 'Sua nova senha deve ter no mínimo 8 caracteres.',
        'no-match' => 'A nova senha não corresponde.',
        'email-label' => 'E-mail',
        'new-label' => 'Nova Senha',
        'min-length' => 'A senha deve ter no mínimo 8 caracteres.',
        'confirm-label' => 'Confirmar Nova Senha',
        'label' => 'Redefinir Senha',
    ],

    'register' => [
        'no-match' => 'Sua senha está incorreta.',
        'namefirst-label' => 'Nome',
        'namelast-label' => 'Sobrenome',
        'email-label' => 'E-mail',
        'username-label' => 'Usuário',
        'password-label' => 'Senha',
        'min-length' => 'As senhas devem ter pelo menos 8 caracteres.',
        'confirm-label' => 'Confirmar Senha',
        'label' => 'Registrar',
    ],
    
    'failed' => 'Nenhuma conta correspondente a essas credenciais foi encontrada.',

    'two_factor' => [
        'label' => 'Token de 2 Fatores',
        'label_help' => 'Esta conta exige uma segunda camada de autenticação para continuar. Digite o código gerado pelo seu dispositivo para concluir este login.',
        'checkpoint_failed' => 'O token de autenticação em dois fatores é inválido.',
    ],

    'throttle' => 'Muitas tentativas de login. Por favor, tente novamente em :seconds segundos.',
    'password_requirements' => 'A senha deve ter no mínimo 8 caracteres e deve ser exclusiva para este site.',
    '2fa_must_be_enabled' => 'O administrador exige que a Autenticação em 2 Fatores esteja ativada na sua conta para usar o Painel.',
];
