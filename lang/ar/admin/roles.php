<?php

return [
    'label' => 'دور',
    'plural-label' => 'الأدوار',
    'all' => 'الكل',
    'select_all' => 'تحديد الكل',
    'deselect_all' => 'إلغاء تحديد الكل',
    'details' => [
        'title' => 'تفاصيل الدور',
        'description' => 'تحدد الأدوار ما يمكن للمستخدم فعله في اللوحة، في منطقة الإدارة وعلى الخوادم التي لا يملكها.',
        'name' => 'الاسم',
        'name_placeholder' => 'فريق الدعم',
        'description_label' => 'الوصف',
        'description_placeholder' => 'الغرض من هذا الدور.',
    ],
    'table' => [
        'name' => 'الاسم',
        'permissions' => 'الصلاحيات',
        'users' => 'المستخدمون',
        'updated' => 'آخر تحديث',
    ],
    'groups' => [
        'management' => [
            'title' => 'التحكم',
            'description' => 'الخوادم والأشخاص الذين يستخدمونها والبنية التحتية التي تعمل عليها.',
        ],
        'service' => [
            'title' => 'إدارة السيرفرات',
            'description' => 'ما يمكن تثبيته على الخادم وكيفية توسيع اللوحة نفسها وتحديثها.',
        ],
        'administration' => [
            'title' => 'الإدارة',
            'description' => 'إعدادات اللوحة العامة ومظهرها ومتابعتها.',
        ],
    ],
    'resources' => [
        'servers' => 'الخوادم',
        'users' => 'المستخدمون',
        'roles' => 'الأدوار',
        'nodes' => 'العقد',
        'locations' => 'المواقع',
        'database_hosts' => 'خوادم قواعد البيانات',
        'nests' => 'الأعشاش',
        'eggs' => 'البيوض',
        'mounts' => 'التوصيلات',
        'alerts' => 'التنبيهات',
        'webhooks' => 'ويب هوك',
        'extensions' => 'ملحقات',
        'activity_log' => 'سجل النشاطات',
        'monitoring' => 'المراقبة',
        'settings' => 'الإعدادات',
        'designify' => 'Designify',
        'updates' => 'تحديثات البرنامج',
    ],
    'actions' => [
        'send_email' => 'إرسال بريد إلكتروني',
        'read' => 'عرض',
        'create' => 'إنشاء',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'assign' => 'التعيين للمستخدمين',
        'manage' => 'إدارة',
        'install' => 'تثبيت التحديثات',
    ],
    'messages' => [
        'in_use' => 'هذا الدور ما زال معيّنًا لمستخدمين. أزله عنهم قبل حذفه.',
    ],
    'user' => [
        'roles' => 'الأدوار',
        'roles_self' => 'لا يمكنك تغيير أدوارك.',
        'none' => 'لا شيء',
    ],
];
