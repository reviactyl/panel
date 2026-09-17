<?php

return [

    'label' => 'Montagem',
    'plural_label' => 'Montagens',

    'sections' => [
        'configuration' => 'Configuração de montagem',
    ],

    'fields' => [
        'name' => 'Nome',
        'description' => 'Descrição',
        'source' => 'Caminho de origem',
        'target' => 'Caminho de destino',
        'read_only' => 'Apenas Leitura',
        'user_mountable' => 'Montável pelo usuário',
    ],

    'helpers' => [
        'name' => 'Um nome único usado para diferenciar esta montaria de outras.',
        'description' => 'Uma descrição mais longa e em linguagem acessível a humanos deste troféu.',
        'source' => 'O caminho do arquivo na máquina host a ser montado nos contêineres.',
        'target' => 'O caminho dentro do contêiner para montar isso.',
        'read_only' => 'Se configurado, o ponto de montagem será somente leitura dentro do contêiner.',
        'user_mountable' => 'Se configurado, os usuários poderão montar isso em seus servidores.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nome',
        'source' => 'Fonte',
        'target' => 'Destino',
        'read_only' => 'Apenas Leitura',
        'user_mountable' => 'Montável pelo usuário',
    ],

    'actions' => [
        'attach_egg' => 'Anexar Ovo',
        'attach_node' => 'Anexar nó',
    ],

];
