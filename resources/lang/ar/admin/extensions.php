<?php

return [

    'label' => 'امتداد',
    'plural-label' => 'ملحقات',

    'columns' => [
        'id' => 'ID',
        'name' => 'اسم',
        'version' => 'إصدار',
        'author' => 'مؤلف',
        'enabled' => 'ممكّن',
        'updated' => 'تم التحديث',
        'manifest_json' => 'بيان JSON',
    ],

    'modals' => [
        'manifest' => 'بيان التمديد',
    ],

    'actions' => [
        'edit' => 'يحرر',
        'upload' => 'رفع',
        'manifest' => 'عرض البيان',
        'disable' => 'إبطال',
        'enable' => 'يُمكَِن',
        'delete' => 'يمسح',
        'close' => 'يغلق',
    ],

    'alerts' => [
        'enabled' => 'تم تمكين الامتداد.',
        'enable_failed' => 'فشل في تمكين الامتداد.',
        'disabled' => 'تم تعطيل الإضافة.',
        'disable_failed' => 'فشل في تعطيل التمديد.',
        'uninstalled' => 'تم إلغاء تثبيت الإضافة.',
        'uninstall_failed' => 'فشل في إلغاء تثبيت الامتداد.',
        'could_not_locate_file' => 'تعذر تحديد موقع ملف الحزمة الذي تم تحميله.',
        'invalid_file_type' => 'يُسمح فقط بملفات ‎.rext.',
        'upload_hint' => 'يُسمح فقط بحزم ملحق .rext.',
        'install_failed' => 'فشل تثبيت الإضافة.',
        'install_success' => 'تم تثبيت :name (:version) بنجاح.',
    ],

];
