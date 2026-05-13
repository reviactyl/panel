<?php

return [
    'navigation' => [
        'label' => 'يراقب',
        'group' => 'إدارة',
    ],

    'page' => [
        'title' => 'يراقب',
        'heading' => 'المراقبة المباشرة',
    ],

    'actions' => [
        'refresh' => 'تحديث البيانات',
    ],

    'selector' => [
        'label' => 'حدد العقدة',
        'placeholder' => 'حدد عقدة...',
    ],

    'stats' => [
        'cpu_usage' => 'استخدام وحدة المعالجة المركزية',
        'cpu_cores' => 'النوى :count المتاحة',
        'memory_usage' => 'استخدام الذاكرة',
        'disk_usage' => 'استخدام القرص',
        'network_traffic' => 'حركة مرور الشبكة',
        'uptime' => 'الجهوزية',
        'goroutines' => ':count goroutines',
        'last_updated' => 'آخر تحديث',
        'no_node' => 'لم يتم تحديد عقدة',
        'no_node_desc' => 'الرجاء تحديد عقدة لعرض بيانات المراقبة',
        'no_node_hint' => 'استخدم القائمة المنسدلة أعلاه',
        'error' => 'خطأ',
        'error_desc' => 'غير قادر على تحميل بيانات المراقبة',
        'error_fetch' => 'غير قادر على جلب البيانات من الوكيل',
        'error_node_gone' => 'العقدة لم تعد موجودة',
    ],

    'details' => [
        'heading' => 'تفاصيل النظام',
        'button' => 'تفاصيل',
        'close' => 'يغلق',
        'no_data' => 'لا توجد بيانات متاحة. تأكد من أن العقدة متصلة بالإنترنت.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'الاستخدام الإجمالي',
        'cpu_cores' => 'النوى',
        'per_core' => 'الاستخدام لكل نواة',

        'memory_section' => 'ذاكرة',
        'total_memory' => 'المجموع',
        'used_memory' => 'مستخدم',
        'free_memory' => 'حر',
        'available_memory' => 'متاح',

        'swap_section' => 'تبديل',
        'swap_none' => 'لم يتم تكوين مبادلة على هذه العقدة.',
        'swap_total' => 'المجموع',
        'swap_used' => 'مستخدم',
        'swap_free' => 'حر',
        'swap_usage' => 'الاستخدام',

        'network_section' => 'شبكة',
        'bytes_sent' => 'البايتات المرسلة',
        'bytes_recv' => 'وحدات البايت المستلمة',
        'packets_sent' => 'الحزم المرسلة',
        'packets_received' => 'الحزم المستلمة',

        'runtime_section' => 'وقت التشغيل',
        'go_version' => 'الذهاب الإصدار',
        'arch' => 'بنيان',
        'goroutines' => 'جوروتينس',
        'uptime' => 'الجهوزية',
    ],
    'servers' => [
        'heading' => 'استخدام الخادم',
        'no_node' => 'حدد عقدة لعرض استخدام الخادم.',
        'no_servers' => 'لم يتم العثور على خوادم على هذه العقدة.',
        'error_fetch' => 'غير قادر على جلب بيانات الخادم من الوكيل.',
        'col' => [
            'name' => 'الخادم',
            'state' => 'ولاية',
            'cpu' => 'CPU',
            'memory' => 'ذاكرة',
            'disk' => 'القرص',
            'network' => 'شبكة',
            'uptime' => 'الجهوزية',
        ],
        'states' => [
            'running' => 'جري',
            'starting' => 'البدء',
            'stopping' => 'وقف',
            'offline' => 'غير متصل',
            'crashed' => 'تحطمت',
            'unknown' => 'مجهول',
        ],
    ],
];
