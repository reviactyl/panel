<?php

return [

    'label' => 'Montering',
    'plural_label' => 'Monteringar',

    'sections' => [
        'configuration' => 'Monteringskonfiguration',
    ],

    'fields' => [
        'name' => 'Namn',
        'description' => 'Beskrivning',
        'source' => 'Källsökväg',
        'target' => 'Målsökväg',
        'read_only' => 'Endast läsning',
        'user_mountable' => 'Användarmontering tillåten',
    ],

    'helpers' => [
        'name' => 'Ett unikt namn som används för att skilja denna montering från en annan.',
        'description' => 'En längre, läsbar beskrivning av denna montering.',
        'source' => 'Filsökvägen på värdmaskinen som ska monteras till containrar.',
        'target' => 'Sökvägen inuti containern där detta ska monteras.',
        'read_only' => 'Om aktiverad kommer monteringen vara skrivskyddad inuti containern.',
        'user_mountable' => 'Om aktiverad kan användare montera detta till sina servrar.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Namn',
        'source' => 'Källa',
        'target' => 'Mål',
        'read_only' => 'Endast läsning',
        'user_mountable' => 'Användarmontering tillåten',
    ],

];
