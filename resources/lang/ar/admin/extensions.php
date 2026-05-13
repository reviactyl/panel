<?php

return [

    'label' => 'إضافة',
    'plural-label' => 'الإضافات',

    'columns' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'version' => 'الإصدار',
        'author' => 'المؤلف',
        'enabled' => 'مفعل',
        'updated' => 'تم التحديث',
        'manifest_json' => 'Manifest JSON',
    ],

    'modals' => [
        'manifest' => 'بيانات الإضافة',
    ],

    'actions' => [
        'edit' => 'تعديل',
        'upload' => 'رفع',
        'manifest' => 'عرض Manifest',
        'disable' => 'تعطيل',
        'enable' => 'تفعيل',
        'delete' => 'حذف',
        'close' => 'إغلاق',
    ],

    'alerts' => [
        'enabled' => 'تم تفعيل الإضافة.',
        'enable_failed' => 'فشل تفعيل الإضافة.',
        'disabled' => 'تم تعطيل الإضافة.',
        'disable_failed' => 'فشل تعطيل الإضافة.',
        'uninstalled' => 'تمت إزالة الإضافة.',
        'uninstall_failed' => 'فشل إزالة الإضافة.',
        'could_not_locate_file' => 'تعذر العثور على ملف الحزمة المرفوعة.',
        'invalid_file_type' => 'يُسمح فقط بملفات .rext.',
        'upload_hint' => 'يُسمح فقط بحزم الإضافات بصيغة .rext.',
        'install_failed' => 'فشل تثبيت الإضافة.',
        'install_success' => 'تم تثبيت :name (:version) بنجاح.',
    ],

];
