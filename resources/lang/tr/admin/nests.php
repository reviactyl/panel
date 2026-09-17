<?php

return [

    'label' => 'Yuva',
    'plural_label' => 'Nestler',

    'sections' => [
        'configuration' => 'Nest Yapılandırması',
    ],

    'fields' => [
        'name' => 'İsim',
        'author' => 'Yazar',
        'description' => 'Açıklama',
    ],

    'helpers' => [
        'name' => 'Bu nest`i tanımlamak için kullanılan benzersiz bir ad.',
        'author' => 'Bu nest`in yazarı. Geçerli bir e-posta olmalıdır.',
        'description' => 'Bu nest`in açıklaması.',
    ],

    'columns' => [
        'id' => 'KİMLİK',
        'name' => 'İsim',
        'author' => 'Yazar',
        'eggs' => 'Eggler',
        'servers' => 'Sunucular',
    ],

    'actions' => [
        'import' => 'Yumurta İthalatı',
    ],

    'import' => [
        'file_label' => 'Yumurta Dosyası (JSON)',
        'nest_label' => 'İlişkili Yuva',
        'file_not_found' => 'Dosya bulunamadı',
        'file_not_found_body' => 'Yüklenen dosya bulunamadı.',
        'invalid_format' => 'Geçersiz dosya biçimi',
        'invalid_format_body' => 'Beklenmeyen dosya formatı alındı.',
        'success' => 'Yumurta başarıyla içe aktarıldı',
        'failed' => 'Yumurta içe aktarılamadı',
    ],

    'notices' => [
        'created' => 'Yeni bir nest, :name, başarıyla oluşturuldu.',
        'deleted' => 'İstenen nest Panelden başarıyla silindi.',
        'updated' => 'Nest yapılandırma seçenekleri başarıyla güncellendi.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'Bu Egg ve ilişkili değişkenleri başarıyla içe aktarıldı.',
            'updated_via_import' => 'Bu Egg, sağlanan dosya kullanılarak güncellendi.',
            'deleted' => 'İstenen egg Panelden başarıyla silindi.',
            'updated' => 'Egg yapılandırması başarıyla güncellendi.',
            'script_updated' => 'Egg kurulum betiği güncellendi ve sunucular kurulduğunda çalışacak.',
            'egg_created' => 'Yeni bir egg başarıyla oluşturuldu. Bu yeni egg\'i uygulamak için çalışan daemon\'ları yeniden başlatmanız gerekecek.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => '":variable" değişkeni silindi ve sunucular yeniden oluşturulduğunda artık kullanılamayacak.',
            'variable_updated' => '":variable" değişkeni güncellendi. Değişiklikleri uygulamak için bu değişkeni kullanan sunucuları yeniden oluşturmanız gerekecek.',
            'variable_created' => 'Yeni değişken başarıyla oluşturuldu ve bu egg\'e atandı.',
        ],
    ],
];
