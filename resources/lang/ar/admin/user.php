<?php

return [
    'title' => 'المستخدم',
    'exceptions' => [
        'delete_self' => 'لا يمكنك حذف حسابك الخاص.',
        'user_has_servers' => 'لا يمكن حذف مستخدم لديه خوادم نشطة مرتبطة بحسابه. يرجى حذف خوادمهم قبل المتابعة.',
    ],
    'notices' => [
        'account_created' => 'تم إنشاء الحساب بنجاح.',
        'account_updated' => 'تم تحديث الحساب بنجاح.',
    ],
    'details' => [
        'account_details' => 'تفاصيل الحساب',
        'external_id' => 'المعرف الخارجي',
        'username' => 'اسم المستخدم',
        'email' => 'عنوان البريد الإلكتروني',
        'first_name' => 'الاسم الأول',
        'last_name' => 'النسب',
        'language' => 'اللغة',
        'geolocate' => 'Geolocate (Automatic)',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'root_admin' => 'المسؤول الجذري (الرئيسي)',
        'root_admin_desc' => 'سيمتلك هذا المستخدم وصولاً كاملاً إلى جميع الخوادم والإعدادات على النظام.',
        'privileges' => 'الصلاحيات',
        'admin_status' => 'حالة المسؤول',
    ],
];
