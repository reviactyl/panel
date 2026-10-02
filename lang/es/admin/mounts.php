<?php

return [

    'label' => 'Montar',
    'plural_label' => 'monturas',

    'sections' => [
        'configuration' => 'Configuración de montaje',
    ],

    'fields' => [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'source' => 'Ruta de origen',
        'target' => 'Ruta de destino',
        'read_only' => 'Solo lectura',
        'user_mountable' => 'Montable por el usuario',
    ],

    'helpers' => [
        'name' => 'Un nombre único utilizado para separar esta montura de otra.',
        'description' => 'Una descripción más larga y legible por humanos de esta montura.',
        'source' => 'La ruta del archivo en la máquina host para montar en contenedores.',
        'target' => 'La ruta dentro del contenedor para montar esto como.',
        'read_only' => 'Si se establece, el montaje será de solo lectura dentro del contenedor.',
        'user_mountable' => 'Si se configura, los usuarios podrán montar esto en sus servidores.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'source' => 'Fuente',
        'target' => 'Objetivo',
        'read_only' => 'Solo lectura',
        'user_mountable' => 'Montable por el usuario',
    ],

    'actions' => [
        'attach_egg' => 'adjuntar huevo',
        'attach_node' => 'Adjuntar nodo',
    ],

];
