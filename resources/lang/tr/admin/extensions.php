<?php

return [

    'label' => 'Eklenti',
    'plural-label' => 'Uzantılar',

    'columns' => [
        'id' => 'ID',
        'name' => 'İsim',
        'version' => 'Sürüm',
        'author' => 'Yazar',
        'enabled' => 'Etkinleştirilmiş',
        'updated' => 'Güncellendi',
        'manifest_json' => 'JSON\'u bildir',
    ],

    'modals' => [
        'manifest' => 'Uzantı Bildirimi',
    ],

    'actions' => [
        'edit' => 'Düzenlemek',
        'upload' => 'Yüklemek',
        'manifest' => 'Manifest\'i Görüntüle',
        'disable' => 'Devre dışı bırakmak',
        'enable' => 'Olanak vermek',
        'delete' => 'Silmek',
        'close' => 'Kapalı',
    ],

    'alerts' => [
        'enabled' => 'Uzantı etkinleştirildi.',
        'enable_failed' => 'Uzantı etkinleştirilemedi.',
        'disabled' => 'Uzantı devre dışı bırakıldı.',
        'disable_failed' => 'Uzantı devre dışı bırakılamadı.',
        'uninstalled' => 'Uzantı kaldırıldı.',
        'uninstall_failed' => 'Uzantı kaldırılamadı.',
        'could_not_locate_file' => 'Yüklenen paket dosyası bulunamadı.',
        'invalid_file_type' => 'Yalnızca .rext dosyalarına izin verilir.',
        'upload_hint' => 'Yalnızca .rext uzantılı paketlere izin verilir.',
        'install_failed' => 'Uzantı yüklemesi başarısız oldu.',
        'install_success' => ':name (:version) başarıyla kuruldu.',
    ],

];
