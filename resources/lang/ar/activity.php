<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'مستخدم النظام',
        'system' => 'النظام',
        'using-api-key' => 'باستخدام مفتاح API',
        'using-sftp' => 'باستخدام SFTP',
    ],
    'auth' => [
        'fail' => 'فشل تسجيل الدخول',
        'success' => 'تم تسجيل الدخول',
        'password-reset' => 'إعادة تعيين كلمة المرور',
        'reset-password' => 'طلب إعادة تعيين كلمة المرور',
        'checkpoint' => 'طلب المصادقة الثنائية',
        'recovery-token' => 'تم استخدام رمز استرداد المصادقة الثنائية',
        'token' => 'تم حل تحدي المصادقة الثنائية',
        'ip-blocked' => 'تم حظر الطلب من عنوان IP غير مدرج لـ :identifier',
        'sftp' => [
            'fail' => 'فشل تسجيل الدخول عبر SFTP',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'تم تغيير البريد الإلكتروني من :old إلى :new',
            'password-changed' => 'تم تغيير كلمة المرور',
            'language-changed' => 'تم تغيير اللغة من :old إلى :new',
        ],
        'api-key' => [
            'create' => 'تم إنشاء مفتاح API جديد :identifier',
            'delete' => 'تم حذف مفتاح API :identifier',
        ],
        'ssh-key' => [
            'create' => 'تمت إضافة مفتاح SSH :fingerprint إلى الحساب',
            'delete' => 'تمت إزالة مفتاح SSH :fingerprint من الحساب',
        ],
        'two-factor' => [
            'create' => 'تم تفعيل المصادقة الثنائية',
            'delete' => 'تم تعطيل المصادقة الثنائية',
        ],
    ],
    'server' => [
        'reinstall' => 'تمت إعادة تثبيت الخادم',
        'console' => [
            'command' => 'تم تنفيذ ":command" على الخادم',
        ],
        'power' => [
            'start' => 'تم بدء تشغيل الخادم',
            'stop' => 'تم إيقاف تشغيل الخادم',
            'restart' => 'تمت إعادة تشغيل الخادم',
            'kill' => 'تم إنهاء عملية الخادم',
        ],
        'backup' => [
            'download' => 'تم تنزيل النسخة الاحتياطية :name',
            'delete' => 'تم حذف النسخة الاحتياطية :name',
            'restore' => 'تمت استعادة النسخة الاحتياطية :name (تم حذف الملفات: :truncate)',
            'restore-complete' => 'اكتملت استعادة النسخة الاحتياطية :name',
            'restore-failed' => 'فشل إكمال استعادة النسخة الاحتياطية :name',
            'start' => 'بدأت نسخة احتياطية جديدة :name',
            'complete' => 'تم وضع علامة اكتمال على النسخة الاحتياطية :name',
            'fail' => 'تم وضع علامة فشل عملية على النسخة الاحتياطية :name',
            'lock' => 'تم قفل النسخة الاحتياطية :name',
            'unlock' => 'تم فتح قفل النسخة الاحتياطية :name',
        ],
        'database' => [
            'create' => 'تم إنشاء قاعدة بيانات جديدة :name',
            'rotate-password' => 'تم تدوير كلمة المرور لقاعدة البيانات :name',
            'delete' => 'تم حذف قاعدة البيانات :name',
        ],
        'file' => [
            'compress_one' => 'Compressed :directory:files.0',
            'compress_other' => 'تم ضغط :count ملف في :directory',
            'read' => 'تم عرض محتويات :file',
            'copy' => 'تم إنشاء نسخة من :file',
            'create-directory' => 'تم إنشاء مجلد :directory:name',
            'decompress' => 'تم فك ضغط :files في :directory',
            'delete_one' => 'تم حذف :directory:files.0',
            'delete_other' => 'تم حذف :count ملف في :directory',
            'download' => 'تم تنزيل :file',
            'pull' => 'تم تنزيل ملف عن بعد من :url إلى :directory',
            'rename_one' => 'تمت إعادة تسمية :directory:files.0.from إلى :directory:files.0.to',
            'rename_other' => 'تمت إعادة تسمية :count ملف في :directory',
            'write' => 'تم كتابة محتوى جديد إلى :file',
            'upload' => 'بدأ رفع ملف',
            'uploaded' => 'تم رفع :directory:file',
        ],
        'sftp' => [
            'denied' => 'تم حظر وصول SFTP بسبب الصلاحيات',
            'create_one' => 'تم إنشاء :files.0',
            'create_other' => 'تم إنشاء :count ملف جديد',
            'write_one' => 'تم تعديل محتويات :files.0',
            'write_other' => 'تم تعديل محتويات :count ملف',
            'delete_one' => 'تم حذف :files.0',
            'delete_other' => 'تم حذف :count ملف',
            'create-directory_one' => 'تم إنشاء دليل :files.0',
            'create-directory_other' => 'تم إنشاء :count دليل',
            'rename_one' => 'تمت إعادة تسمية :files.0.from إلى :files.0.to',
            'rename_other' => 'تمت إعادة تسمية أو نقل :count ملف',
        ],
        'allocation' => [
            'create' => 'تمت إضافة :allocation إلى الخادم',
            'notes' => 'تم تحديث ملاحظات :allocation من `:old` إلى `:new`',
            'primary' => 'تم تعيين :allocation كتخصيص أساسي للخادم',
            'delete' => 'تم حذف التخصيص :allocation',
        ],
        'schedule' => [
            'create' => 'تم إنشاء الجدول الزمني :name',
            'update' => 'تم تحديث الجدول الزمني :name',
            'execute' => 'تم تنفيذ الجدول الزمني :name يدوياً',
            'delete' => 'تم حذف الجدول الزمني :name',
        ],
        'task' => [
            'create' => 'تم إنشاء مهمة جديدة `:action` للجدول الزمني :name',
            'update' => 'تم تحديث المهمة `:action` للجدول الزمني :name',
            'delete' => 'تم حذف مهمة للجدول الزمني :name',
        ],
        'settings' => [
            'rename' => 'تمت إعادة تسمية الخادم من :old إلى :new',
            'description' => 'تم تغيير وصف الخادم من :old إلى :new',
        ],
        'startup' => [
            'edit' => 'تم تغيير المتغير :variable من `:old` إلى `:new`',
            'image' => 'تم تحديث صورة دوكر للخادم من :old إلى :new',
        ],
        'subuser' => [
            'create' => 'تمت إضافة :email كمستخدم فرعي',
            'update' => 'تم تحديث صلاحيات المستخدم الفرعي لـ :email',
            'delete' => 'تمت إزالة :email كمستخدم فرعي',
        ],
    ],
];
