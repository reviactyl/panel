<?php

return [
    'navigation' => [
        'label' => 'المراقبة',
        'group' => 'الإدارة',
    ],

    'page' => [
        'title' => 'المراقبة',
        'heading' => 'المراقبة المباشرة',
    ],

    'actions' => [
        'refresh' => 'تحديث البيانات',
    ],

    'selector' => [
        'label' => 'اختر العقدة',
        'placeholder' => 'اختر عقدة...',
    ],

    'stats' => [
        'cpu_usage' => 'استخدام المعالج',
        'cpu_cores' => ':count نواة متاحة',
        'memory_usage' => 'استخدام الذاكرة',
        'disk_usage' => 'استخدام القرص',
        'network_traffic' => 'حركة الشبكة',
        'uptime' => 'مدة التشغيل',
        'goroutines' => ':count goroutines',
        'last_updated' => 'آخر تحديث',
        'no_node' => 'لم يتم اختيار عقدة',
        'no_node_desc' => 'يرجى اختيار عقدة لعرض بيانات المراقبة',
        'no_node_hint' => 'استخدم القائمة المنسدلة أعلاه',
        'error' => 'خطأ',
        'error_desc' => 'تعذر تحميل بيانات المراقبة',
        'error_fetch' => 'تعذر جلب البيانات من Agent',
        'error_node_gone' => 'العقدة لم تعد موجودة',
    ],

    'details' => [
        'heading' => 'تفاصيل النظام',
        'button' => 'التفاصيل',
        'close' => 'إغلاق',
        'no_data' => 'لا توجد بيانات متاحة. تأكد من أن العقدة متصلة.',

        'cpu_section' => 'المعالج',
        'cpu_total' => 'إجمالي الاستخدام',
        'cpu_cores' => 'الأنوية',
        'per_core' => 'استخدام كل نواة',

        'memory_section' => 'الذاكرة',
        'total_memory' => 'الإجمالي',
        'used_memory' => 'المستخدم',
        'free_memory' => 'المتاح',
        'available_memory' => 'القابل للاستخدام',

        'swap_section' => 'Swap',
        'swap_none' => 'لا يوجد Swap مكوَّن على هذه العقدة.',
        'swap_total' => 'الإجمالي',
        'swap_used' => 'المستخدم',
        'swap_free' => 'المتاح',
        'swap_usage' => 'الاستخدام',

        'network_section' => 'الشبكة',
        'bytes_sent' => 'البايتات المرسلة',
        'bytes_recv' => 'البايتات المستلمة',
        'packets_sent' => 'الحزم المرسلة',
        'packets_received' => 'الحزم المستلمة',

        'runtime_section' => 'بيئة التشغيل',
        'go_version' => 'إصدار Go',
        'arch' => 'المعمارية',
        'goroutines' => 'Goroutines',
        'uptime' => 'مدة التشغيل',
    ],
    'servers' => [
        'heading' => 'استخدام الخوادم',
        'no_node' => 'اختر عقدة لعرض استخدام الخوادم.',
        'no_servers' => 'لم يتم العثور على خوادم على هذه العقدة.',
        'error_fetch' => 'تعذر جلب بيانات الخوادم من Agent.',
        'col' => [
            'name' => 'الخادم',
            'state' => 'الحالة',
            'cpu' => 'المعالج',
            'memory' => 'الذاكرة',
            'disk' => 'القرص',
            'network' => 'الشبكة',
            'uptime' => 'مدة التشغيل',
        ],
        'states' => [
            'running' => 'قيد التشغيل',
            'starting' => 'جارٍ التشغيل',
            'stopping' => 'جارٍ الإيقاف',
            'offline' => 'غير متصل',
            'crashed' => 'متعطل',
            'unknown' => 'غير معروف',
        ],
    ],
];
