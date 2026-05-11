<?php

return [
    'label' => 'خادم',
    'plural-label' => 'الخوادم',

    'sections' => [
        'identity' => [
            'title' => 'الهوية',
            'description' => 'معلومات أساسية عن الخادم والملكية.',
        ],
        'allocation' => [
            'title' => 'التخصيص',
            'description' => 'اختر العقدة وتخصيص الشبكة لهذا الخادم.',
        ],
        'startup' => [
            'title' => 'بدء التشغيل',
            'description' => 'قم بتكوين البيضة، أمر بدء التشغيل، وصورة دوكر.',
        ],
        'resources' => [
            'title' => 'حدود الموارد',
            'description' => 'حدد حدود موارد الخادم.',
        ],
        'feature_limits' => [
            'title' => 'حدود الميزات',
            'description' => 'حدد قواعد قواعد البيانات، التخصيصات، والنسخ الاحتياطية.',
        ],
        'environment' => [
            'title' => 'متغيرات البيئة',
            'description' => 'اضبط قيم البيئة للبيضة المختارة.',
        ],
    ],

    'status' => [
        'online' => 'Online',
        'offline' => 'Offline',
        'starting' => 'Starting',
        'stopping' => 'Stopping',
        'crashed' => 'Crashed',
        'installing' => 'Installing',
        'restoring_backup' => 'Restoring Backup',
        'install_failed' => 'Install Failed',
        'reinstall_failed' => 'Reinstall Failed',
        'suspended' => 'Suspended',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'Core Details',
            'allocation' => 'Allocation Management',
            'feature_limits' => 'Application Feature Limits',
            'resources' => 'Resource Management',
            'nest' => 'Nest Configuration',
            'docker' => 'Docker Configuration',
            'startup' => 'Startup Configuration',
            'variables' => 'Service Variables',
        ],

        'fields' => [
            'name' => [
                'label' => 'Server Name',
                'placeholder' => 'Server Name',
                'helper' => 'Character limits: a-z A-Z 0-9 _ - . and spaces.',
            ],
            'owner' => [
                'label' => 'Server Owner',
                'helper' => 'Email address of the Server Owner.',
            ],
            'description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'start_on_completion' => [
                'label' => 'Start Server when Installed',
            ],
            'node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be deployed to.',
            ],
            'allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on creation.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'cpu' => [
                'label' => 'CPU Limit',
                'helper' => 'Set 0 for no CPU limit. A full virtual core is 100%.',
            ],
            'threads' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: use a single number or comma separated list, for example 0, 0-1,3, or 0,1,3,4.',
            ],
            'memory' => [
                'label' => 'Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk' => [
                'label' => 'Disk Space',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'oom_disabled' => [
                'label' => 'Enable OOM Killer',
                'helper' => 'Terminates the server if it breaches memory limits.',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'Select the Nest that this server will be grouped under.',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'Select the Egg that will define how this server should operate.',
            ],
            'skip_scripts' => [
                'label' => 'Skip Egg Install Script',
                'helper' => 'If the selected Egg has an install script attached to it, the script will run during install unless this is checked.',
            ],
            'image' => [
                'label' => 'Docker Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Docker Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the default Docker image that will be used to run this server.',
            ],
            'startup' => [
                'label' => 'Startup Command',
                'helper' => 'Available substitutes: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'environment_placeholder' => [
                'label' => 'Select an egg to configure service variables',
            ],
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'الوضع المتقدم',
            'helper' => 'تبديل لإظهار خيارات تكوين الخادم الإضافية. قم بالتشغيل فقط إذا كنت تفهم تبعات الإعدادات الإضافية.',
        ],
        'external_id' => [
            'label' => 'المعرف الخارجي',
            'helper' => 'معرّف فريد اختياري لهذا الخادم.',
        ],
        'owner' => [
            'label' => 'المالك',
            'helper' => 'اختر المستخدم الذي يمتلك هذا الخادم.',
        ],
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'اسم الخادم',
            'helper' => 'اسم قصير لهذا الخادم.',
        ],
        'description' => [
            'label' => 'وصف',
            'placeholder' => 'وصف الخادم',
            'helper' => 'وصف اختياري لهذا الخادم.',
        ],
        'node' => [
            'label' => 'العقد',
            'helper' => 'العقدة التي سيتم نشر هذا الخادم عليها.',
        ],
        'allocation' => [
            'label' => 'التخصيص الأساسي',
            'helper' => 'تخصيص IP/Port الافتراضي لهذا الخادم.',
        ],
        'additional_allocations' => [
            'label' => 'تخصيصات إضافية',
            'helper' => 'تخصيصات إضافية اختيارية للتعيين.',
        ],
        'nest' => [
            'label' => 'العش',
            'helper' => 'خدمة العش لهذا الخادم.',
        ],
        'egg' => [
            'label' => 'القوالب',
            'helper' => 'البيضة التي تحدد سلوك الخادم.',
        ],
        'startup' => [
            'label' => 'أمر بدء التشغيل',
            'helper' => 'أمر بدء التشغيل للخادم.',
        ],
        'image' => [
            'label' => 'صورة دوكر',
            'placeholder' => 'e.g. ghcr.io/pterodactyl/yolks:java_17',
            'helper' => 'صورة دوكر المستخدمة لتشغيل هذا الخادم.',
            'custom' => 'مخصص',
        ],
        'skip_scripts' => [
            'label' => 'تجاوز سكربتات البيضة',
            'helper' => 'تخطي سكربتات تثبيت البيضة أثناء الإنشاء.',
        ],
        'start_on_completion' => [
            'label' => 'البدء عند الاكتمال',
            'helper' => 'تشغيل الخادم تلقائيًا بعد التثبيت.',
        ],
        'memory' => [
            'label' => 'الذاكرة',
            'helper' => 'إجمالي تخصيص الذاكرة. اضبط على 0 لغير محدود. (الذاكرة غير المحدودة لا تعمل لبيوض مينيكرافت بسبب أمر بدء التشغيل)',
        ],
        'swap' => [
            'label' => 'الذاكرة الافتراضية',
            'helper' => 'تخصيص ذاكرة الافتراضية. اضبط على 0 لتعطيل الذاكرة الافتراضية أو -1 للسماح بذكارة افتراضية غير محدودة.',
        ],
        'disk' => [
            'label' => 'القرص',
            'helper' => 'مساحة القرص المخصصة. اضبط على 0 لغير محدود.',
        ],
        'io' => [
            'label' => 'وزن الإدخال/الإخراج',
            'helper' => 'أولوية الإدخال/الإخراج النسبية للقرص (10-1000).',
        ],
        'cpu' => [
            'label' => 'المعالج',
            'helper' => 'حد المعالج بالنسبة المئوية. 100% تعني نواة كاملة واحدة، 200% تعني نواتين كاملتين، إلخ.',
        ],
        'enter_size_in_gib' => [
            'label' => 'أدخل الحجم بالجيجابايت',
            'helper' => 'يمكنك إدخال الأحجام بالجيجابايت باستخدام اللاحقة \'GiB\' (مثال: 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'أنوية المعالج',
            'helper' => 'تثبيت اختياري للأنوية. مثال: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'تعطيل OOM Killer',
            'helper' => 'منع النواة من قتل العملية عندما.',
        ],
        'database_limit' => [
            'label' => 'حد قواعد البيانات',
            'helper' => 'الحد الأقصى لعدد قواعد البيانات.',
        ],
        'allocation_limit' => [
            'label' => 'حد التخصيصات',
            'helper' => 'الحد الأقصى لعدد التخصيصات.',
        ],
        'backup_limit' => [
            'label' => 'حد النسخ الاحتياطية',
            'helper' => 'الحد الأقصى لعدد النسخ الاحتياطية.',
        ],
        'environment' => [
            'key' => 'المتغير',
            'value' => 'القيمة',
            'helper' => 'متغيرات البيئة لهذه البيضة.',
        ],
        'use_custom_image' => [
            'label' => 'استخدام صورة مخصصة',
            'helper' => 'بدّل لاستخدام صورة دوكر مخصصة بدلاً من تلك المقدمة من البيضة.',
        ],
    ],

    'table' => [
        'id' => 'المعرف',
        'name' => 'الاسم',
        'owner' => 'المالك',
        'node' => 'العقدة',
        'allocation' => 'التخصيص',
        'status' => 'الحالة',
        'egg' => 'البيضة',
        'memory' => 'الذاكرة',
        'disk' => 'القرص',
        'cpu' => 'المعالج',
        'created' => 'تاريخ الإنشاء',
        'updated' => 'آخر تحديث',
        'installed' => 'تم التثبيت',
        'no_status' => 'لا توجد حالة',
        'unlimited' => 'Unlimited',
    ],

    'messages' => [
        'created' => 'تم إنشاء الخادم بنجاح.',
        'updated' => 'تم تحديث الخادم بنجاح.',
        'deleted' => 'تم حذف الخادم بنجاح.',
    ],

    'actions' => [
        'edit' => 'تعديل',
        'toggle_install_status' => 'تبديل حالة التثبيت',
        'suspend' => 'تعليق',
        'unsuspend' => 'إلغاء التعليق',
        'suspended' => 'معلق',
        'unsuspended' => 'غير معلق',
        'reinstall' => 'إعادة تثبيت',
        'delete' => 'حذف',
        'delete_forcibly' => 'حذف قسري',
        'view' => 'عرض',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'أنت تحاول حذف التخصيص الافتراضي لهذا الخادم ولكن لا يوجد تخصيص احتياطي لاستخدامه.',
        'marked_as_failed' => 'تم وضع علامة على هذا الخادم بأنه فشل في التثبيت السابق. لا يمكن تبديل الحالة الحالية في هذه الحالة.',
        'bad_variable' => 'حدث خطأ في التحقق من صحة المتغير :name.',
        'daemon_exception' => 'حدث استثناء أثناء محاولة الاتصال بالدايمون مما أدى إلى الحصول على رمز استجابة HTTP/:code. تم تسجيل هذا الاستثناء. (معرف الطلب: :request_id)',
        'default_allocation_not_found' => 'التخصيص الافتراضي المطلوب غير موجود في تخصيصات هذا الخادم.',
    ],

    'alerts' => [
        'install_toggled' => 'تم تبديل حالة تثبيت الخادم.',
        'server_suspended' => 'تم :action الخادم.',
        'server_reinstalled' => 'تم بدء إعادة تثبيت الخادم.',
        'server_deleted' => 'تم حذف الخادم.',
        'server_delete_failed' => 'فشل حذف الخادم.',
        'startup_changed' => 'تم تحديث تكوين بدء التشغيل لهذا الخادم. إذا تم تغيير عش أو بيضة هذا الخادم، فسيتم إجراء إعادة تثبيت الآن.',
        'server_created' => 'تم إنشاء الخادم بنجاح على اللوحة. يرجى منح الدايمون بضع دقائق لتثبيت هذا الخادم بالكامل.',
        'build_updated' => 'تم تحديث تفاصيل البناء لهذا الخادم. قد تتطلب بعض التغييرات إعادة تشغيل لتصبح سارية المفعول.',
        'suspension_toggled' => 'تم تغيير حالة تعليق الخادم إلى :status.',
        'rebuild_on_boot' => 'تم وضع علامة على هذا الخادم بأنه يتطلب إعادة بناء حاوية Docker. سيحدث هذا في المرة التالية التي يتم فيها تشغيل الخادم.',
        'details_updated' => 'تم تحديث تفاصيل الخادم بنجاح.',
        'docker_image_updated' => 'تم تغيير صورة دوكر الافتراضية لاستخدامها لهذا الخادم بنجاح. يلزم إعادة التشغيل لتطبيق هذا التغيير.',
        'node_required' => 'يجب أن يكون لديك عقدة واحدة على الأقل مكونة قبل أن تتمكن من إضافة خادم إلى هذه اللوحة.',
        'transfer_nodes_required' => 'يجب أن يكون لديك عقدتان على الأقل مكونتان قبل أن تتمكن من نقل الخوادم.',
        'transfer_started' => 'تم بدء نقل الخادم.',
        'transfer_not_viable' => 'العقدة التي حددتها لا تحتوي على مساحة القرص أو الذاكرة المطلوبة المتاحة لاستيعاب هذا الخادم.',
        'primary_allocation_updated' => 'Primary allocation updated.',
        'database_created' => 'Database created.',
        'database_password_reset' => 'Database password reset.',
        'database_deleted' => 'Database deleted.',
    ],

    'edit' => [
        'tabs' => [
            'information' => 'Information',
            'build_configuration' => 'Build Configuration',
            'startup' => 'Startup',
            'manage' => 'Manage',
        ],

        'sections' => [
            'resource_management' => 'Resource Management',
            'application_feature_limits' => 'Application Feature Limits',
            'allocation_management' => 'Allocation Management',
            'startup_command_modification' => 'Startup Command Modification',
            'service_configuration' => 'Service Configuration',
            'docker_image_configuration' => 'Docker Image Configuration',
            'service_variables' => 'Service Variables',
            'reinstall_server' => 'Reinstall Server',
            'install_status' => 'Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'delete_server' => 'Delete Server',
        ],

        'section_descriptions' => [
            'service_configuration' => 'Changing these values can trigger a reinstall. The server will be stopped immediately for that operation.',
            'reinstall_server' => 'This will reinstall the server with the assigned service scripts. This could overwrite server data.',
            'install_status' => 'Change install status from uninstalled to installed, or vice versa.',
            'suspend_server' => 'This will stop running processes and block the user from managing the server through the panel or API.',
            'unsuspend_server' => 'This will unsuspend the server and restore normal user access.',
            'transfer_server_transferring' => 'This server is currently being transferred to another node.',
            'transfer_server' => 'Transfer this server to another node connected to this panel.',
            'delete_server' => 'This permanently deletes the server from the panel and Agent. Force delete skips Agent deletion if necessary.',
        ],

        'fields' => [
            'server_name' => [
                'label' => 'Server Name',
                'helper' => 'Character limits: a-zA-Z0-9_-, spaces, and standard printable characters.',
            ],
            'server_owner' => [
                'label' => 'Server Owner',
                'helper' => 'Changing ownership automatically revokes daemon tokens for the previous owner.',
            ],
            'server_description' => [
                'label' => 'Server Description',
                'helper' => 'A brief description of this server.',
            ],
            'server_uuid' => [
                'label' => 'Server UUID',
            ],
            'server_uuid_short' => [
                'label' => 'Server UUID (Short)',
            ],
            'external_identifier' => [
                'label' => 'External Identifier',
                'helper' => 'Leave empty to not assign an external identifier. The external ID should be unique to this server.',
            ],
            'game_port' => [
                'label' => 'Game Port',
                'helper' => 'The default connection address that will be used for this game server.',
            ],
            'additional_ports' => [
                'label' => 'Additional Ports',
                'helper' => 'Assign or remove extra ports. Identical ports on different IPs cannot be assigned to the same server.',
            ],
            'startup_command' => [
                'label' => 'Startup Command',
                'helper' => 'Available by default: {{SERVER_MEMORY}}, {{SERVER_IP}}, and {{SERVER_PORT}}.',
            ],
            'default_startup_command' => [
                'label' => 'Default Startup Command',
                'error' => 'ERROR: Startup Not Defined!',
            ],
            'cpu_limit' => [
                'label' => 'CPU Limit',
                'helper' => 'Each virtual core is 100%. Set 0 for unrestricted CPU time.',
            ],
            'cpu_pinning' => [
                'label' => 'CPU Pinning',
                'helper' => 'Advanced: leave blank for all cores. Examples: 0, 0-1,3, or 0,1,3,4.',
            ],
            'allocated_memory' => [
                'label' => 'Allocated Memory',
                'helper' => 'The maximum amount of memory allowed for this container. Set 0 for unlimited.',
            ],
            'allocated_swap' => [
                'label' => 'Allocated Swap',
                'helper' => 'Set 0 to disable swap, or -1 to allow unlimited swap.',
            ],
            'disk_space_limit' => [
                'label' => 'Disk Space Limit',
                'helper' => 'Set 0 to allow unlimited disk usage.',
            ],
            'block_io_proportion' => [
                'label' => 'Block IO Proportion',
                'helper' => 'Advanced: IO performance relative to other running containers. Value should be 10 to 1000.',
            ],
            'disable_oom_killer' => [
                'label' => 'Disable OOM Killer',
                'helper' => 'Enabling OOM killer may cause server processes to exit unexpectedly.',
            ],
            'database_limit' => [
                'label' => 'Database Limit',
                'helper' => 'The total number of databases a user is allowed to create for this server.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'helper' => 'The total number of allocations a user is allowed to create for this server.',
            ],
            'backup_limit' => [
                'label' => 'Backup Limit',
                'helper' => 'The total number of backups that can be created for this server.',
            ],
            'image' => [
                'label' => 'Image',
                'helper' => 'Select an image from the dropdown, or enter a custom image below.',
            ],
            'custom_image' => [
                'label' => 'Custom Image',
                'placeholder' => 'Or enter a custom image...',
                'helper' => 'This is the Docker image that will be used to run this server.',
            ],
            'transfer_node' => [
                'label' => 'Node',
                'helper' => 'The node which this server will be transferred to.',
            ],
            'transfer_allocation' => [
                'label' => 'Default Allocation',
                'helper' => 'The main allocation that will be assigned to this server.',
            ],
            'transfer_additional_allocations' => [
                'label' => 'Additional Allocation(s)',
                'helper' => 'Additional allocations to assign to this server on transfer.',
            ],
        ],

        'actions' => [
            'reinstall_server' => 'Reinstall Server',
            'toggle_install_status' => 'Toggle Install Status',
            'suspend_server' => 'Suspend Server',
            'unsuspend_server' => 'Unsuspend Server',
            'transfer_server' => 'Transfer Server',
            'confirm' => 'Confirm',
            'delete_server' => 'Delete Server',
            'forcibly_delete_server' => 'Forcibly Delete Server',
        ],
    ],

    'allocations' => [
        'title' => 'Allocations',

        'table' => [
            'ip' => 'IP',
            'port' => 'Port',
            'alias' => 'Alias',
            'primary' => 'Primary',
            'notes' => 'Notes',
            'created' => 'Created',
        ],

        'placeholder' => [
            'no_alias_assigned' => 'No Alias Assigned',
        ],

        'actions' => [
            'make_primary' => 'Make Primary',
        ],
    ],

    'databases' => [
        'title' => 'Databases',

        'table' => [
            'database' => 'Database',
            'username' => 'Username',
            'remote' => 'Remote',
            'host' => 'Host',
            'max_connections' => 'Max Connections',
            'created' => 'Created',
        ],

        'placeholder' => [
            'unlimited' => 'Unlimited',
        ],

        'actions' => [
            'create_database' => 'Create Database',
            'reset_password' => 'Reset Password',
            'delete' => 'Delete',
        ],

        'create_modal' => [
            'database_name' => [
                'label' => 'Database Name',
                'helper' => 'The panel will prefix this with the server ID, matching the old admin panel.',
            ],
            'database_host' => [
                'label' => 'Database Host',
            ],
            'remote' => [
                'label' => 'Remote',
            ],
            'max_connections' => [
                'label' => 'Max Connections',
            ],
        ],
    ],
];
