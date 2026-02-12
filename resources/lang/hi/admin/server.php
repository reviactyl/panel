<?php

return [
    'label' => 'Server',
    'plural-label' => 'Servers',

    'sections' => [
        'identity' => [
            'title' => 'Identity',
            'description' => 'Basic server information and ownership.',
        ],
        'allocation' => [
            'title' => 'Allocation',
            'description' => 'Select the node and network allocation for this server.',
        ],
        'startup' => [
            'title' => 'Startup',
            'description' => 'Configure the egg, startup command, and Docker image.',
        ],
        'resources' => [
            'title' => 'Resource Limits',
            'description' => 'Define the server resource limits.',
        ],
        'feature_limits' => [
            'title' => 'Feature Limits',
            'description' => 'Limit databases, allocations, and backups.',
        ],
        'environment' => [
            'title' => 'Environment Variables',
            'description' => 'Set environment values for the selected egg.',
        ],
    ],

    'fields' => [
        'advanced_mode' => [
            'label' => 'Advanced Mode',
            'helper' => 'Toggle to show additional server configuration options. Toggle on only if you understand the implications of the additional settings.',
        ],
        'external_id' => [
            'label' => 'External ID',
            'helper' => 'Optional unique identifier for this server.',
        ],
        'owner' => [
            'label' => 'Owner',
            'helper' => 'Select the user that owns this server.',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Server Name',
            'helper' => 'A short name for this server.',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Server description',
            'helper' => 'Optional description for this server.',
        ],
        'node' => [
            'label' => 'Node',
            'helper' => 'The node this server will be deployed to.',
        ],
        'allocation' => [
            'label' => 'Primary Allocation',
            'helper' => 'The default IP/port allocation for this server.',
        ],
        'additional_allocations' => [
            'label' => 'Additional Allocations',
            'helper' => 'Optional extra allocations to assign.',
        ],
        'nest' => [
            'label' => 'Nest',
            'helper' => 'The service nest for this server.',
        ],
        'egg' => [
            'label' => 'Egg',
            'helper' => 'The egg that defines server behavior.',
        ],
        'startup' => [
            'label' => 'Startup Command',
            'helper' => 'The startup command for the server.',
        ],
        'image' => [
            'label' => 'Docker Image',
            'placeholder' => 'e.g. ghcr.io/pterodactyl/yolks:java_17',
            'helper' => 'Docker image used to run this server.',
            'custom' => 'Custom',
        ],
        'skip_scripts' => [
            'label' => 'Skip Egg Scripts',
            'helper' => 'Skip egg install scripts during creation.',
        ],
        'start_on_completion' => [
            'label' => 'Start on Completion',
            'helper' => 'Automatically start the server after installation.',
        ],
        'memory' => [
            'label' => 'Memory',
            'helper' => 'Total memory allocation. Set to 0 for unlimited. (Unlimited Memory doesn\'t work for Minecraft Eggs due to Startup Command)',
        ],
        'swap' => [
            'label' => 'Swap',
            'helper' => 'Swap memory allocation. Set to 0 to disable swap or -1 to allow unlimited swap.',
        ],
        'disk' => [
            'label' => 'Disk',
            'helper' => 'Disk space allocation. Set to 0 for unlimited.',
        ],
        'io' => [
            'label' => 'IO Weight',
            'helper' => 'Relative disk I/O priority (10-1000).',
        ],
        'cpu' => [
            'label' => 'CPU',
            'helper' => 'CPU limit in percent. 100% means one full core, 200% means two full cores, etc.',
        ],
        'enter_size_in_gib' => [
            'label' => 'Enter size in GiB',
            'helper' => 'You can enter sizes in GiB by using the "GiB" suffix (e.g. 10GiB = 10240MiB).',
        ],
        'threads' => [
            'label' => 'CPU Threads',
            'helper' => 'Optional thread pinning. Example: 0-1,3.',
        ],
        'oom_disabled' => [
            'label' => 'Disable OOM Killer',
            'helper' => 'Prevent the kernel from killing the process when out of memory.',
        ],
        'database_limit' => [
            'label' => 'Database Limit',
            'helper' => 'Maximum number of databases.',
        ],
        'allocation_limit' => [
            'label' => 'Allocation Limit',
            'helper' => 'Maximum number of allocations.',
        ],
        'backup_limit' => [
            'label' => 'Backup Limit',
            'helper' => 'Maximum number of backups.',
        ],
        'environment' => [
            'key' => 'Variable',
            'value' => 'Value',
            'helper' => 'Environment variables for this egg.',
        ],
        'use_custom_image' => [
            'label' => 'Use Custom Image',
            'helper' => 'Toggle to use a custom Docker image instead of one provided by the egg.',
        ],
    ],

    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'owner' => 'Owner',
        'node' => 'Node',
        'allocation' => 'Allocation',
        'status' => 'Status',
        'egg' => 'Egg',
        'memory' => 'Memory',
        'disk' => 'Disk',
        'cpu' => 'CPU',
        'created' => 'Created',
        'updated' => 'Updated',
        'installed' => 'Installed',
        'no_status' => 'No Status',
    ],

    'messages' => [
        'created' => 'Server has been successfully created.',
        'updated' => 'Server has been successfully updated.',
        'deleted' => 'Server has been successfully deleted.',
    ],

    'actions' => [
        'edit' => 'Edit',
        'toggle_install_status' => 'Toggle Install Status',
        'suspend' => 'Suspend',
        'unsuspend' => 'Unsuspend',
        'suspended' => 'Suspended',
        'unsuspended' => 'Unsuspended',
        'reinstall' => 'Reinstall',
        'delete' => 'Delete',
        'delete_forcibly' => 'Forcibly Delete',
        'view' => 'View',
    ],

    'exceptions' => [
        'no_new_default_allocation' => 'आप इस सर्वर के लिए डिफ़ॉल्ट आवंटन हटाने का प्रयास कर रहे हैं लेकिन उपयोग करने के लिए कोई फ़ॉलबैक आवंटन नहीं है।',
        'marked_as_failed' => 'इस सर्वर को पिछली इंस्टॉलेशन में विफल के रूप में चिह्नित किया गया था। इस स्थिति में वर्तमान स्थिति को टॉगल नहीं किया जा सकता।',
        'bad_variable' => ':name वेरिएबल के साथ एक सत्यापन त्रुटि थी।',
        'daemon_exception' => 'डेमन के साथ संवाद करने का प्रयास करते समय एक अपवाद था जिसके परिणामस्वरूप HTTP/:code प्रतिक्रिया कोड मिला। यह अपवाद लॉग किया गया है। (अनुरोध आईडी: :request_id)',
        'default_allocation_not_found' => 'अनुरोधित डिफ़ॉल्ट आवंटन इस सर्वर के आवंटनों में नहीं मिला।',
    ],

    'alerts' => [
        'install_toggled' => 'इस सर्वर के लिए इंस्टॉलेशन स्थिति टॉगल कर दी गई है।',
        'server_suspended' => 'Server has been :action.',
        'server_reinstalled' => 'इस सर्वर को अभी से शुरू होने वाली पुनर्स्थापना के लिए कतारबद्ध कर दिया गया है।',
        'server_deleted' => 'सर्वर को सिस्टम से सफलतापूर्वक हटा दिया गया है।',
        'server_delete_failed' => 'Failed to delete server.',
        'startup_changed' => 'इस सर्वर के लिए स्टार्टअप कॉन्फ़िगरेशन अपडेट कर दिया गया है। यदि इस सर्वर का नेस्ट या egg बदला गया था तो अब एक पुनर्स्थापना होगी।',
        'server_created' => 'सर्वर पैनल पर सफलतापूर्वक बनाया गया। कृपया डेमन को इस सर्वर को पूरी तरह से इंस्टॉल करने के लिए कुछ मिनट दें।',
        'build_updated' => 'इस सर्वर के बिल्ड विवरण अपडेट कर दिए गए हैं। कुछ परिवर्तनों को प्रभावी होने के लिए पुनः आरंभ की आवश्यकता हो सकती है।',
        'suspension_toggled' => 'सर्वर निलंबन स्थिति :status में बदल दी गई है।',
        'rebuild_on_boot' => 'इस सर्वर को Docker कंटेनर पुनर्निर्माण की आवश्यकता के रूप में चिह्नित किया गया है। यह अगली बार सर्वर शुरू होने पर होगा।',
        'details_updated' => 'सर्वर विवरण सफलतापूर्वक अपडेट कर दिए गए हैं।',
        'docker_image_updated' => 'इस सर्वर के लिए उपयोग करने के लिए डिफ़ॉल्ट Docker इमेज सफलतापूर्वक बदल दी गई। इस परिवर्तन को लागू करने के लिए रीबूट आवश्यक है।',
        'node_required' => 'इस पैनल में सर्वर जोड़ने से पहले आपके पास कम से कम एक नोड कॉन्फ़िगर होना चाहिए।',
        'transfer_nodes_required' => 'सर्वर ट्रांसफर करने से पहले आपके पास कम से कम दो नोड कॉन्फ़िगर होने चाहिए।',
        'transfer_started' => 'सर्वर ट्रांसफर शुरू हो गया है।',
        'transfer_not_viable' => 'आपने जो नोड चुना है उसमें इस सर्वर को समायोजित करने के लिए आवश्यक डिस्क स्पेस या मेमोरी उपलब्ध नहीं है।',
    ],
];
