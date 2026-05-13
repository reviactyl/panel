<?php

return [

    'label' => 'Extensão',
    'plural-label' => 'Extensões',

    'columns' => [
        'id' => 'ID',
        'name' => 'Nome',
        'version' => 'Versão',
        'author' => 'Autor',
        'enabled' => 'Habilitado',
        'updated' => 'Atualizado',
        'manifest_json' => 'Manifesto JSON',
    ],

    'modals' => [
        'manifest' => 'Manifesto de Extensão',
    ],

    'actions' => [
        'edit' => 'Editar',
        'upload' => 'Carregar',
        'manifest' => 'Ver manifesto',
        'disable' => 'Desativar',
        'enable' => 'Habilitar',
        'delete' => 'Excluir',
        'close' => 'Fechar',
    ],

    'alerts' => [
        'enabled' => 'Extensão habilitada.',
        'enable_failed' => 'Falha ao ativar a extensão.',
        'disabled' => 'Extensão desativada.',
        'disable_failed' => 'Falha ao desativar a extensão.',
        'uninstalled' => 'Extensão desinstalada.',
        'uninstall_failed' => 'Falha ao desinstalar a extensão.',
        'could_not_locate_file' => 'Não foi possível localizar o arquivo do pacote carregado.',
        'invalid_file_type' => 'Somente arquivos .rext são permitidos.',
        'upload_hint' => 'Somente pacotes de extensão .rext são permitidos.',
        'install_failed' => 'Falha na instalação da extensão.',
        'install_success' => ':name (:version) instalado com sucesso.',
    ],

];
