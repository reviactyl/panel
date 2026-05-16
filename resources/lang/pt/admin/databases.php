<?php

return [

    'label' => 'Banco de Dados',
    'plural-label' => 'Bancos de Dados',

    'none' => 'Nenhum',

    'sections' => [
        'host_details' => [
            'title' => 'Detalhes do Host',
            'description' => 'Configure as informações do host de banco de dados',
        ],

        'authentication' => [
            'title' => 'Autenticação',
        ],

        'linked_node' => [
            'title' => 'Node Vinculado',
        ],
    ],

    'placeholders' => [
        'name' => 'MySQL Produção',
    ],

    'helpers' => [
        'host' => 'O nome do host ou o endereço IP do servidor de banco de dados.',
        'linked_node' => 'Opcional. Vincule este host a um nó específico.',
    ],

    'fields' => [
        'linked_node' => 'Node Vinculado',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nome',
        'host' => 'Hospedar',
        'port' => 'Porta',
        'username' => 'Usuário',
        'linked_node' => 'Host Vinculado',
        'databases' => 'Banco de Dados',
        'created' => 'Criar',
    ],

    'actions' => [
        'edit' => 'Editar',
        'delete' => 'Apagar',
    ],

    'errors' => [
        'cannot_delete' => 'Não é possível excluir um host de banco de dados com os bancos de dados associados.',
    ],

];
