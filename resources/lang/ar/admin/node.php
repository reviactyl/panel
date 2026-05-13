<?php

return [
    'label' => 'العقد',
    'plural-label' => 'العقد',

    'sections' => [
        'overview' => [
            'title' => 'نظرة عامة',
            'information-label' => 'معلومات العقدة',
            'version-label' => 'إصدار Agent',
            'architecture-label' => 'المعمارية',
            'kernel-label' => 'النواة',
            'cpus-label' => 'خيوط المعالج',
            'cpu-usage-label' => 'استخدام المعالج',
            'memory-usage-label' => 'استخدام الذاكرة',
            'disk-usage-label' => 'استخدام القرص',
        ],
        'tabs' => [
            'title' => 'إعدادات العقدة',
        ],
        'identity' => [
            'title' => 'الهوية',
            'description' => 'معلومات العقدة الأساسية وتفاصيل الاتصال.',
        ],
        'connection' => [
            'title' => 'تفاصيل الاتصال',
            'description' => 'إعداد طريقة الاتصال بهذه العقدة.',
        ],
        'resources' => [
            'title' => 'تخصيص الموارد',
            'description' => 'تحديد حدود الذاكرة ومساحة التخزين لهذه العقدة.',
        ],
        'daemon' => [
            'title' => 'إعدادات خدمة Daemon',
            'description' => 'ضبط الإعدادات الخاصة بالخدمة.',
        ],
        'configuration' => [
            'title' => 'الإعدادات',
            'config_description' => 'ملف الإعدادات',
            'deploy_description' => 'أنشئ أمر نشر مخصص يمكن استخدامه لإعداد Agent على الخادم المستهدف.',
        ],
    ],

    'fields' => [
        'uuid' => [
            'label' => 'UUID',
        ],
        'public' => [
            'label' => 'عام',
            'helper' => 'عند تعيين العقدة كخاصة، سيتم منع النشر التلقائي عليها',
        ],
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'اسم العقدة',
            'helper' => 'اسم وصفي لهذه العقدة.',
        ],
        'description' => [
            'label' => 'الوصف',
            'placeholder' => 'وصف العقدة',
            'helper' => 'وصف اختياري لهذه العقدة.',
        ],
        'location' => [
            'label' => 'الموقع',
            'helper' => 'الموقع الذي تم تعيين هذه العقدة إليه.',
        ],
        'fqdn' => [
            'label' => 'FQDN',
            'placeholder' => 'node.example.com',
            'helper' => 'اسم نطاق كامل أو عنوان IP.',
        ],
        'ssl' => [
            'label' => 'يستخدم SSL',
            'helper' => 'ما إذا كانت الخدمة على هذه العقدة مهيأة لاستخدام SSL للاتصال الآمن.',
            'helper_forced' => 'هذه اللوحة تعمل عبر HTTPS لذلك يتم فرض SSL على هذه العقدة.',
        ],
        'behind_proxy' => [
            'label' => 'خلف وكيل بروكسي',
            'helper' => 'قم بالتفعيل إذا كانت هذه العقدة خلف وكيل مثل Cloudflare.',
        ],
        'maintenance_mode' => [
            'label' => 'وضع الصيانة',
            'helper' => 'منع إنشاء خوادم جديدة على هذه العقدة.',
        ],
        'memory' => [
            'label' => 'إجمالي الذاكرة',
            'helper' => 'إجمالي الذاكرة المتاحة على هذه العقدة بوحدة MiB.',
        ],
        'memory_overallocate' => [
            'label' => 'تجاوز تخصيص الذاكرة',
            'helper' => 'نسبة السماح بتجاوز تخصيص الذاكرة. استخدم -1 لتعطيل التحقق.',
        ],
        'disk' => [
            'label' => 'إجمالي مساحة التخزين',
            'helper' => 'إجمالي مساحة التخزين المتاحة على هذه العقدة بوحدة MiB.',
        ],
        'disk_overallocate' => [
            'label' => 'تجاوز تخصيص القرص',
            'helper' => 'نسبة السماح بتجاوز تخصيص القرص. استخدم -1 لتعطيل التحقق.',
        ],
        'upload_size' => [
            'label' => 'الحد الأقصى لحجم الرفع',
            'helper' => 'أقصى حجم مسموح لرفع الملفات عبر لوحة الويب.',
        ],
        'daemon_base' => [
            'label' => 'المجلد الأساسي',
            'placeholder' => '/home/daemon-files',
            'helper' => 'المجلد الذي يتم فيه تخزين ملفات الخوادم.',
        ],
        'daemon_listen' => [
            'label' => 'منفذ خدمة Daemon',
            'helper' => 'المنفذ الذي تستمع عليه الخدمة لاتصالات HTTP.',
        ],
        'daemon_sftp' => [
            'label' => 'منفذ SFTP',
            'helper' => 'المنفذ المستخدم لاتصالات SFTP.',
        ],
        'daemon_token_id' => [
            'label' => 'معرف التوكن',
        ],
        'container_text' => [
            'label' => 'بادئة الحاوية',
            'helper' => 'بادئة النص المعروضة في أسماء الحاويات.',
        ],
    ],

    'table' => [
        'health' => 'الحالة',
        'health_http_status' => 'HTTP :status',
        'health_error' => ':error',
        'health_check_console' => 'تحقق من وحدة تحكم المتصفح',
        'id' => 'المعرف',
        'uuid' => 'UUID',
        'name' => 'الاسم',
        'location' => 'الموقع',
        'fqdn' => 'FQDN',
        'scheme' => 'البروتوكول',
        'public' => 'عام',
        'behind_proxy' => 'خلف بروكسي',
        'maintenance_mode' => 'الصيانة',
        'memory' => 'الذاكرة',
        'memory_overallocate' => 'تجاوز الذاكرة',
        'disk' => 'القرص',
        'disk_overallocate' => 'تجاوز القرص',
        'upload_size' => 'حجم الرفع',
        'daemon_listen' => 'منفذ الدايمون',
        'daemon_sftp' => 'منفذ SFTP',
        'daemon_base' => 'المجلد الأساسي',
        'servers' => 'الخوادم',
        'created' => 'تاريخ الإنشاء',
        'updated' => 'آخر تحديث',
    ],

    'filters' => [
        'public' => 'عام',
        'maintenance' => 'الصيانة',
        'public_true' => 'عام',
        'public_false' => 'خاص',
        'maintenance_true' => 'تحت الصيانة',
        'maintenance_false' => 'نشط',
    ],

    'actions' => [
        'create' => 'إنشاء',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'view' => 'عرض',
        'random' => 'عشوائي',
        'view_monitoring' => 'عرض المراقبة',
    ],

    'deployment' => [
        'generate_label' => 'إنشاء توكن النشر',
        'modal_heading' => 'أمر النشر التلقائي',
        'modal_description' => 'قم بتشغيل هذا الأمر على عقدتك لإعداد Agent تلقائيًا.',
        'modal_close' => 'إغلاق',
        'command_label' => 'أمر النشر',
        'command_helper' => 'انسخ هذا الأمر وقم بتشغيله على خادم العقدة الخاص بك.',
        'token_success' => 'تم إنشاء التوكن بنجاح',
        'token_success_body' => 'انسخ وشغل الأمر أدناه على عقدتك.',
        'save_first' => 'يرجى حفظ العقدة أولاً.',
        'auto_generated_key' => 'مفتاح نشر العقدة تم إنشاؤه تلقائيًا.',
        'error' => 'حدث خطأ أثناء إنشاء التوكن. يرجى المحاولة مرة أخرى.',
    ],

    'general' => [
        'na' => 'غير متوفر',
        'unavailable' => 'غير متاح',
    ],

    'messages' => [
        'created' => 'تم إنشاء العقدة بنجاح.',
        'updated' => 'تم تحديث العقدة بنجاح.',
        'deleted' => 'تم حذف العقدة بنجاح.',
        'cannot_delete_with_servers' => 'لا يمكن حذف عقدة تحتوي على خوادم نشطة.',
    ],

    'allocations' => [
        'label' => 'التخصيصات',
        'table' => [
            'ip' => 'IP',
            'port' => 'المنفذ',
            'alias' => 'الاسم المستعار',
            'server' => 'الخادم',
            'notes' => 'ملاحظات',
            'created' => 'تاريخ الإنشاء',
            'unassigned' => 'غير مخصص',
        ],
        'fields' => [
            'allocation_ip' => [
                'label' => 'عنوان IP',
                'helper' => 'يدعم IP واحد أو CIDR (مثال: 192.0.2.1 أو 192.0.2.0/24).',
            ],
            'allocation_ports' => [
                'label' => 'المنافذ',
                'helper' => 'أدخل المنافذ أو النطاقات (مثال: 25565, 25566, 25570-25580).',
            ],
            'allocation_alias' => [
                'label' => 'الاسم المستعار لـ IP',
                'helper' => 'اسم مستعار اختياري للعرض بدلاً من IP.',
            ],
        ],
        'actions' => [
            'add' => 'إضافة تخصيص',
            'delete' => 'حذف',
        ],
        'messages' => [
            'created' => 'تمت إضافة التخصيصات.',
            'deleted' => 'تم حذف التخصيص.',
            'failed' => 'فشل إجراء التخصيص.',
        ],
    ],

    'validation' => [
        'fqdn_not_resolvable' => 'اسم المضيف المؤهل بالكامل (FQDN) أو عنوان IP المقدم لا يشير إلى عنوان IP صالح.',
        'fqdn_required_for_ssl' => 'اسم مجال مؤهل بالكامل يشير إلى عنوان IP عام مطلوب لاستخدام SSL لهذه العقدة.',
    ],
    'notices' => [
        'allocations_added' => 'تمت إضافة التخصيصات بنجاح إلى هذه العقدة.',
        'node_deleted' => 'تمت إزالة العقدة بنجاح من اللوحة.',
        'location_required' => 'يجب أن يكون لديك موقع واحد على الأقل مهيأ قبل أن تتمكن من إضافة عقدة إلى هذه اللوحة.',
        'node_created' => 'تم إنشاء عقدة جديدة بنجاح. يمكنك تكوين الدايمون تلقائيًا على هذا الجهاز بزيارة علامة التبويب `الإعدادات`. قبل أن تتمكن من إضافة أي خوادم، يجب عليك أولاً تخصيص عنوان IP واحد ومنفذ واحد على الأقل.',
        'node_updated' => 'تم تحديث معلومات العقدة. إذا تم تغيير أي إعدادات للدايمون، فستحتاج إلى إعادة تشغيله حتى تدخل هذه التغييرات حيز التنفيذ.',
        'unallocated_deleted' => 'تم حذف جميع المنافذ غير المخصصة لـ <code>:ip</code>.',
    ],
];
