<?php

return [

    'label' => 'Konum',
    'plural-label' => 'Konumlar',

    'section' => [
        'title' => 'Konum Detayları',
        'description' => 'Düğümlerin atanabileceği bir konum tanımlayın.',
    ],

    'fields' => [
        'short' => [
            'label' => 'Kısa Kod',
            'placeholder' => 'us.nyc.1',
            'helper' => 'Bu konum için kısa bir tanımlayıcı.',
        ],

        'long' => [
            'label' => 'Açıklama',
            'placeholder' => 'New York City, NY, ABD',
            'helper' => 'Bu konumun daha uzun bir açıklaması.',
        ],
    ],

    'table' => [
        'id' => 'KİMLİK',
        'short' => 'Kısa Kod',
        'long' => 'Açıklama',
        'nodes' => 'Düğümler',
        'servers' => 'Sunucular',
        'created' => 'Oluşturuldu',
    ],

    'actions' => [
        'edit' => 'Düzenle',
        'delete' => 'Sil',
    ],

    'messages' => [
        'cannot_delete_with_nodes' => 'İlişkili düğümlere sahip bir konum silinemez.',
    ],

];
