<?php

return [

    'label' => 'قاعدة بيانات',
    'plural-label' => 'قواعد بيانات',

    'none' => 'لا شيء',

    'sections' => [
        'host_details' => [
            'title' => 'تفاصيل الخادم',
            'description' => 'ضبط إعدادات اتصال خادم قاعدة البيانات.',
        ],

        'authentication' => [
            'title' => 'المصادقة',
        ],

        'linked_node' => [
            'title' => 'العقدة المرتبطة',
        ],
    ],

    'placeholders' => [
        'name' => 'الإنتاجية قاعدة بيانات',
    ],

    'helpers' => [
        'host' => 'اسم المضيف أو عنوان IP لخادم قاعدة البيانات.',
        'linked_node' => 'اختياري. اربط هذا الخادم بعقدة محددة.',
    ],

    'fields' => [
        'linked_node' => 'العقدة المرتبطة',
    ],

    'columns' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'host' => 'هوست',
        'port' => 'بورت',
        'username' => 'اسم المستخدم',
        'linked_node' => 'العقدة المرتبطة',
        'databases' => 'قواعد البيانات',
        'created' => 'تاريخ الإنشاء',
    ],

    'actions' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
    ],

    'errors' => [
        'cannot_delete' => 'لا يمكن حذف خادم قاعدة البيانات إذا كانت هناك قواعد بيانات مرتبطة به.',
    ],

];
