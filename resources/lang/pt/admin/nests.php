<?php

return [
    
    'label' => 'Nest',
    'plural_label' => 'Nests',

    'sections' => [
        'configuration' => 'Configurações do Nest',
    ],

    'fields' => [
        'name' => 'Nome',
        'author' => 'Autor',
        'description' => 'Descrição',
    ],

    'helpers' => [
        'name' => 'Um nome único usado para identificar este Nest.',
        'author' => 'O autor deste Nest. Deve ser um e-mail válido.',
        'description' => 'Descrição deste Nest.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nome',
        'author' => 'Autor',
        'eggs' => 'Eggs',
        'servers' => 'Servidores',
    ],
    
    'actions' => [
        'import' => 'Import Egg',
    ],

    'import' => [
        'file_label' => 'Egg File (JSON)',
        'nest_label' => 'Associated Nest',
        'file_not_found' => 'File not found',
        'file_not_found_body' => 'Could not locate uploaded file.',
        'invalid_format' => 'Invalid file format',
        'invalid_format_body' => 'Unexpected file format received.',
        'success' => 'Egg imported successfully',
        'failed' => 'Failed to import egg',
    ],

    'notices' => [
        'created' => 'Um novo ninho, :name, foi criado com sucesso.',
        'deleted' => 'O ninho solicitado foi excluído com sucesso do Painel.',
        'updated' => 'As opções de configuração do ninho foram atualizadas com sucesso.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Este Egg e suas variáveis associadas foram importados com sucesso.',
            'updated_via_import' => 'Este Egg foi atualizado usando o arquivo fornecido.',
            'deleted' => 'O egg solicitado foi excluído com sucesso do Painel.',
            'updated' => 'A configuração do Egg foi atualizada com sucesso.',
            'script_updated' => 'O script de instalação do Egg foi atualizado e será executado sempre que os servidores forem instalados.',
            'egg_created' => 'Um novo egg foi criado com sucesso. Você precisará reiniciar quaisquer daemons em execução para aplicar este novo egg.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'A variável ":variable" foi excluída e não estará mais disponível para os servidores após a reconstrução.',
            'variable_updated' => 'A variável ":variable" foi atualizada. Você precisará reconstruir quaisquer servidores usando esta variável para aplicar as alterações.',
            'variable_created' => 'Nova variável foi criada com sucesso e atribuída a este egg.',
        ],
    ],
];
