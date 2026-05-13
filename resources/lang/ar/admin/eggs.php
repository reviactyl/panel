<?php

return [

    'tabs' => [
        'configuration' => 'إعدادات البيضة',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'الإعدادات',
        ],
        'identity' => [
            'title' => 'الهوية',
        ],
        'docker_images' => [
            'title' => 'صور Docker',
            'description' => 'صور Docker المتاحة للخوادم التي تستخدم هذه البيضة. أدخل صورة واحدة في كل سطر.',
        ],
        'process_management' => [
            'title' => 'إدارة العمليات',
        ],
        'variables' => [
            'title' => 'المتغيرات',
        ],
        'install_script' => [
            'title' => 'سكريبت التثبيت',
        ],
    ],

    'fields' => [
        'nest' => 'العش',
        'uuid' => 'UUID',
        'name' => 'الاسم',
        'author' => 'المؤلف',
        'image' => 'الصورة',
        'description' => 'الوصف',
        'image_name' => 'اسم الصورة',
        'image_uri' => 'رابط الصورة',
        'add_docker_image' => 'إضافة صورة Docker',
        'force_outgoing_ip' => 'فرض عنوان IP الصادر',
        'features' => 'الميزات',
        'startup' => 'أمر التشغيل',
        'config_stop' => 'أمر الإيقاف',
        'config_from' => 'نسخ الإعدادات من',
        'config_startup' => 'إعدادات التشغيل (JSON)',
        'config_logs' => 'إعدادات السجلات (JSON)',
        'config_files' => 'ملفات الإعدادات (JSON)',
        'file_denylist' => 'قائمة الملفات المحظورة',
        'env_variable' => 'متغير البيئة',
        'user_viewable' => 'يمكن للمستخدمين العرض',
        'user_editable' => 'يمكن للمستخدمين التعديل',
        'rules' => 'قواعد الإدخال',
        'default_value' => 'القيمة الافتراضية',
        'script_install' => 'سكريبت التثبيت',
        'script_container' => 'حاوية السكريبت',
        'script_entry' => 'أمر نقطة دخول السكريبت',
        'copy_script_from' => 'نسخ السكريبت من',
        'script_is_privileged' => 'مميز',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'يفرض أن تكون جميع حركة الشبكة الصادرة بعنوان IP المصدر المترجم NAT إلى عنوان IP الخاص بالتخصيص الأساسي للخادم.',
        'features' => 'ميزات إضافية تابعة لهذه البيضة. مفيدة لتكوين تعديلات إضافية على اللوحة.',
        'file_denylist' => 'الملفات التي لا يجب أن يتمكن المستخدم من تعديلها.',
        'script_is_privileged' => 'تشغيل سكريبت التثبيت داخل حاوية مميزة (root).',
    ],

    'actions' => [
        'export' => 'تصدير',
        'create' => 'إنشاء بيضة',
        'edit' => 'تعديل',
    ],

    'notices' => [
        'cannot_delete' => 'لا يمكن حذف البيضة',
        'cannot_delete_body' => 'هذه البيضة مرتبطة بـ :count خادم(ات). يرجى حذفها أو إعادة تعيينها أولاً.',
        'cannot_delete_multiple' => 'لا يمكن حذف البيوض المرتبطة بخوادم',
        'cannot_delete_multiple_body' => 'تم تخطي :count بيضة(ات) لأنها مرتبطة بخوادم.',
    ],

];
