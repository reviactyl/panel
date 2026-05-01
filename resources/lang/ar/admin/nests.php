<?php

return [

    'label' => 'العش',
    'plural_label' => 'الأعشاش',

    'sections' => [
        'configuration' => 'إعدادات البيئة',
    ],

    'fields' => [
        'name' => 'الاسم',
        'author' => 'المؤلف',
        'description' => 'الوصف',
    ],

    'helpers' => [
        'name' => 'اسم فريد يستخدم لتعريف هذه البيئة.',
        'author' => 'مؤلف هذه البيئة يجب أن يكون بريدا إلكترونيا صالحا.',
        'description' => 'وصف هذه البيئة.',
    ],

    'columns' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'author' => 'المؤلف',
        'eggs' => 'القوالب',
        'servers' => 'الخوادم',
    ],

    'actions' => [
        'import' => 'Import Egg',
    ],

    'import' => [
        'file_label' => 'Egg File (JSON)',
        'nest_label' => 'Associated Nest',
        'file_not_found' => 'File not found',
        'file_not_found_body' => 'Could not locate uploaded file.',
        'invalid_format' => 'Invalid file format',
        'invalid_format_body' => 'Unexpected file format received.',
        'success' => 'Egg imported successfully',
        'failed' => 'Failed to import egg',
    ],

    'notices' => [
        'created' => 'تم إنشاء بيئة جديدة باسم :name بنجاح.',
        'deleted' => 'تم حذف البيئة المطلوبة من اللوحة بنجاح.',
        'updated' => 'تم تحديث إعدادات البيئة بنجاح.',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'تم استيراد هذا القالب والمتغيرات المرتبطة به بنجاح.',
            'updated_via_import' => 'تم تحديث هذا القالب باستخدام الملف المرفق.',
            'deleted' => 'تم حذف القالب المطلوب من اللوحة بنجاح.',
            'updated' => 'تم تحديث إعدادات القالب بنجاح.',
            'script_updated' => 'تم تحديث سكربت تثبيت القالب وسيتم تشغيله عند تثبيت أي خادم جديد.',
            'egg_created' => 'تم إنشاء قالب جديد بنجاح. ستحتاج إلى إعادة تشغيل أي خدمات (daemons) تعمل حاليا لتطبيق هذا القالب الجديد.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'تم حذف المتغير ":variable" ولن يكون متاحا للخوادم بعد إعادة بنائها.',
            'variable_updated' => 'تم تحديث المتغير ":variable". ستحتاج إلى إعادة بناء أي خوادم تستخدم هذا المتغير لتطبيق التغييرات.',
            'variable_created' => 'تم إنشاء متغير جديد وتعيينه لهذا القالب بنجاح.',
        ],
    ],
];
