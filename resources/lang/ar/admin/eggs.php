<?php

return [

    'tabs' => [
        'configuration' => 'تكوين البيض',
    ],

    'sections' => [
        'configuration' => [
            'title' => 'إعدادات',
        ],
        'identity' => [
            'title' => 'هوية',
        ],
        'docker_images' => [
            'title' => 'صور عامل الميناء',
            'description' => 'صور عامل الإرساء متاحة للخوادم التي تستخدم هذه البيضة. أدخل واحدًا في كل سطر.',
        ],
        'process_management' => [
            'title' => 'إدارة العمليات',
        ],
        'variables' => [
            'title' => 'المتغيرات',
        ],
        'install_script' => [
            'title' => 'تثبيت البرنامج النصي',
        ],
    ],

    'fields' => [
        'nest' => 'عش',
        'uuid' => 'UUID',
        'name' => 'اسم',
        'author' => 'مؤلف',
        'image' => 'صورة',
        'description' => 'وصف',
        'image_name' => 'اسم الصورة',
        'image_uri' => 'صورة URI',
        'add_docker_image' => 'إضافة صورة عامل الميناء',
        'force_outgoing_ip' => 'فرض IP الصادر',
        'features' => 'سمات',
        'startup' => 'أمر بدء التشغيل',
        'config_stop' => 'أمر الإيقاف',
        'config_from' => 'نسخ الإعدادات من',
        'config_startup' => 'بدء التكوين (JSON)',
        'config_logs' => 'تكوين السجل (JSON)',
        'config_files' => 'ملفات التكوين (JSON)',
        'file_denylist' => 'قائمة رفض الملف',
        'env_variable' => 'متغير البيئة',
        'user_viewable' => 'يمكن للمستخدمين المشاهدة',
        'user_editable' => 'يمكن للمستخدمين التحرير',
        'rules' => 'قواعد الإدخال',
        'default_value' => 'القيمة الافتراضية',
        'script_install' => 'تثبيت البرنامج النصي',
        'script_container' => 'حاوية البرنامج النصي',
        'script_entry' => 'أمر نقطة إدخال البرنامج النصي',
        'copy_script_from' => 'نسخ البرنامج النصي من',
        'script_is_privileged' => 'متميز',
    ],

    'helpers' => [
        'force_outgoing_ip' => 'يفرض على كل حركة مرور الشبكة الصادرة أن يكون عنوان IP المصدر الخاص بها مطابقًا لعنوان IP الخاص بتخصيص IP الأساسي للخادم.',
        'features' => 'ميزات إضافية تابعة للبيضة. مفيد لتكوين تعديلات اللوحة الإضافية.',
        'file_denylist' => 'الملفات التي لا ينبغي للمستخدم تحريرها.',
        'script_is_privileged' => 'قم بتشغيل البرنامج النصي للتثبيت كحاوية مميزة (جذر).',
    ],

    'actions' => [
        'export' => 'يصدّر',
        'create' => 'اصنع بيضة',
        'edit' => 'يحرر',
    ],

    'notices' => [
        'cannot_delete' => 'لا يمكن حذف البيضة',
        'cannot_delete_body' => 'هذه البيضة مرتبطة بخادم (خوادم) :count. يرجى حذفها أو إعادة تعيينها أولاً.',
        'cannot_delete_multiple' => 'لا يمكن حذف البيض مع الخوادم',
        'cannot_delete_multiple_body' => 'تحتوي بيضة (بيض) :count على خوادم مرتبطة وتم تخطيها.',
    ],

];
