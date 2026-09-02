<?php

return [

    'label' => 'Extensão',
    'plural-label' => 'Extensões',
    'marketplace_heading' => 'Extensões disponíveis',

    'columns' => [
        'icon' => 'Ícone',
        'id' => 'ID',
        'name' => 'Nome',
        'version' => 'Versão',
        'author' => 'Autor',
        'enabled' => 'Habilitado',
        'updated' => 'Atualizado',
        'last_updated' => 'Última atualização',
        'downloads' => 'Downloads',
        'manifest_json' => 'Manifesto JSON',
        'file' => 'Escolha um arquivo .rext para enviar',
    ],

    'modals' => [
        'manifest' => 'Manifesto de Extensão',
    ],

    'actions' => [
        'edit' => 'Editar',
        'view' => 'Baixe a extensão',
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
