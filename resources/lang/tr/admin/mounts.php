<?php

return [

    'label' => 'Bağlama Noktası',
    'plural_label' => 'Bağlama Noktaları',

    'sections' => [
        'configuration' => 'Montaj Yapılandırması',
    ],

    'fields' => [
        'name' => 'İsim',
        'description' => 'Açıklama',
        'source' => 'Kaynak Yolu',
        'target' => 'Hedef Yolu',
        'read_only' => 'Salt Okunur',
        'user_mountable' => 'Kullanıcı Bağlayabilir',
    ],

    'helpers' => [
        'name' => 'Bu bağlama noktasını dğerlerinden ayırmak için kullanılan benzersiz bir ad.',
        'description' => 'Bu bağlama noktasının daha uzun, okunabilir açıklaması.',
        'source' => 'Konteynerlere bağlanacak ana makinedeki dosya yolu.',
        'target' => 'Bunun konteyner içinde bağlanacağı yol.',
        'read_only' => 'Ayarlanırsa, bağlama noktası konteyner içinde salt okunur olur.',
        'user_mountable' => 'Ayarlanırsa, kullanıcılar bunu sunucularına bağlayabilecekler.',
    ],

    'columns' => [
        'id' => 'KİMLİK',
        'name' => 'İsim',
        'source' => 'Kaynak',
        'target' => 'Hedef',
        'read_only' => 'Salt Okunur',
        'user_mountable' => 'Kullanıcı Bağlayabilir',
    ],

    'actions' => [
        'attach_egg' => 'Yumurtayı Tak',
        'attach_node' => 'Düğüm Ekle',
    ],

];
