<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'Usuário do Sistema',
        'system' => 'Sistema',
        'using-api-key' => 'Usando Chave de API',
        'using-sftp' => 'Usando SFTP',
    ],
    'auth' => [
        'fail' => 'Falha ao entrar',
        'success' => 'Entrou com sucesso',
        'password-reset' => 'Senha redefinida',
        'reset-password' => 'Solicitou redefinição de senha',
        'checkpoint' => 'Autenticação em Dois Fatores solicitada',
        'recovery-token' => 'Usou token de recuperação da Autenticação em Dois Fatores',
        'token' => 'Desafio da Autenticação em Dois Fatores resolvido',
        'ip-blocked' => 'Solicitação bloqueada de IP não listado para :identifier',
        'sftp' => [
            'fail' => 'Falha ao entrar via SFTP',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'E-mail alterado de :old para :new',
            'password-changed' => 'Senha alterada',
        ],
        'api-key' => [
            'create' => 'Criada nova chave de API :identifier',
            'delete' => 'Excluída chave de API :identifier',
        ],
        'ssh-key' => [
            'create' => 'Adicionada chave SSH :fingerprint à conta',
            'delete' => 'Removida chave SSH :fingerprint da conta',
        ],
        'two-factor' => [
            'create' => 'Autenticação em Dois Fatores ativada',
            'delete' => 'Autenticação em Dois Fatores desativada',
        ],
    ],
    'server' => [
        'reinstall' => 'Servidor reinstalado',
        'console' => [
            'command' => 'Executado ":command" no servidor',
        ],
        'power' => [
            'start' => 'Servidor iniciado',
            'stop' => 'Servidor parado',
            'restart' => 'Servidor reiniciado',
            'kill' => 'Processo do servidor finalizado',
        ],
        'backup' => [
            'download' => 'Backup :name baixado',
            'delete' => 'Backup :name excluído',
            'restore' => 'Backup :name restaurado (arquivos excluídos: :truncate)',
            'restore-complete' => 'Restauração do backup :name concluída',
            'restore-failed' => 'Falha ao concluir restauração do backup :name',
            'start' => 'Iniciado novo backup :name',
            'complete' => 'Backup :name marcado como concluído',
            'fail' => 'Backup :name marcado como falhado',
            'lock' => 'Backup :name bloqueado',
            'unlock' => 'Backup :name desbloqueado',
        ],
        'database' => [
            'create' => 'Criado novo banco de dados :name',
            'rotate-password' => 'Senha rotacionada para o banco de dados :name',
            'delete' => 'Banco de dados :name excluído',
        ],
        'file' => [
            'compress_one' => 'Arquivo :directory:file compactado',
            'compress_other' => 'Compactados :count arquivos em :directory',
            'read' => 'Visualizado o conteúdo de :file',
            'copy' => 'Criada cópia de :file',
            'create-directory' => 'Criado diretório :directory:name',
            'decompress' => 'Descompactados :files em :directory',
            'delete_one' => 'Excluído :directory:files.0',
            'delete_other' => 'Excluídos :count arquivos em :directory',
            'download' => 'Arquivo :file baixado',
            'pull' => 'Arquivo remoto baixado de :url para :directory',
            'rename_one' => 'Renomeado :directory:files.0.from para :directory:files.0.to',
            'rename_other' => 'Renomeados :count arquivos em :directory',
            'write' => 'Novo conteúdo escrito em :file',
            'upload' => 'Iniciado upload de arquivo',
            'uploaded' => 'Arquivo :directory:file enviado',
        ],
        'sftp' => [
            'denied' => 'Acesso SFTP bloqueado devido a permissões',
            'create_one' => 'Criado :files.0',
            'create_other' => 'Criados :count novos arquivos',
            'write_one' => 'Conteúdo de :files.0 modificado',
            'write_other' => 'Conteúdo de :count arquivos modificado',
            'delete_one' => 'Excluído :files.0',
            'delete_other' => 'Excluídos :count arquivos',
            'create-directory_one' => 'Diretório :files.0 criado',
            'create-directory_other' => 'Criados :count diretórios',
            'rename_one' => 'Renomeado :files.0.from para :files.0.to',
            'rename_other' => 'Renomeados ou movidos :count arquivos',
        ],
        'allocation' => [
            'create' => 'Adicionado :allocation ao servidor',
            'notes' => 'Notas atualizadas para :allocation de ":old" para ":new"',
            'primary' => 'Definido :allocation como alocação primária do servidor',
            'delete' => 'Alocação :allocation excluída',
        ],
        'schedule' => [
            'create' => 'Criado agendamento :name',
            'update' => 'Agendamento :name atualizado',
            'execute' => 'Agendamento :name executado manualmente',
            'delete' => 'Agendamento :name excluído',
        ],
        'task' => [
            'create' => 'Criada nova tarefa ":action" para o agendamento :name',
            'update' => 'Tarefa ":action" atualizada para o agendamento :name',
            'delete' => 'Tarefa excluída do agendamento :name',
        ],
        'settings' => [
            'rename' => 'Servidor renomeado de :old para :new',
            'description' => 'Descrição do servidor alterada de :old para :new',
        ],
        'startup' => [
            'edit' => 'Variável :variable alterada de ":old" para ":new"',
            'image' => 'Imagem Docker do servidor atualizada de :old para :new',
        ],
        'subuser' => [
            'create' => 'Adicionado :email como subusuário',
            'update' => 'Permissões do subusuário :email atualizadas',
            'delete' => 'Removido :email como subusuário',
        ],
    ],
];
