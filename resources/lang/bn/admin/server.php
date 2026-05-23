<?php

return [
    'label' => 'সার্ভার',
    'plural-label' => 'সার্ভারসমূহ',

    'sections' => [
        'identity' => [
            'title' => 'পরিচিতি',
            'description' => 'মৌলিক সার্ভার তথ্য এবং মালিকানা।',
        ],
        'allocation' => [
            'title' => 'অ্যালোকেশন',
            'description' => 'এই সার্ভারের জন্য নোড এবং নেটওয়ার্ক অ্যালোকেশন নির্বাচন করুন।',
        ],
        'startup' => [
            'title' => 'স্টার্টআপ',
            'description' => 'Egg, স্টার্টআপ কমান্ড এবং Docker ইমেজ কনফিগার করুন।',
        ],
        'resources' => [
            'title' => 'রিসোর্স সীমা',
            'description' => 'সার্ভারের রিসোর্স সীমা নির্ধারণ করুন।',
        ],
        'feature_limits' => [
            'title' => 'ফিচার সীমা',
            'description' => 'ডাটাবেস, অ্যালোকেশন এবং ব্যাকআপ সীমাবদ্ধ করুন।',
        ],
        'environment' => [
            'title' => 'এনভায়রনমেন্ট ভেরিয়েবল',
            'description' => 'নির্বাচিত egg এর জন্য এনভায়রনমেন্ট মান নির্ধারণ করুন।',
        ],
    ],

    'status' => [
        'online' => 'অনলাইন',
        'offline' => 'অফলাইন',
        'starting' => 'চালু হচ্ছে',
        'stopping' => 'বন্ধ হচ্ছে',
        'crashed' => 'ক্র্যাশ হয়েছে',
        'installing' => 'ইনস্টল হচ্ছে',
        'restoring_backup' => 'ব্যাকআপ পুনরুদ্ধার হচ্ছে',
        'install_failed' => 'ইনস্টল ব্যর্থ হয়েছে',
        'reinstall_failed' => 'পুনরায় ইনস্টল ব্যর্থ হয়েছে',
        'suspended' => 'সাসপেন্ড করা হয়েছে',
    ],

    'create' => [
        'sections' => [
            'core_details' => 'মূল বিবরণ',
            'allocation' => 'অ্যালোকেশন ব্যবস্থাপনা',
            'feature_limits' => 'অ্যাপ্লিকেশন ফিচার সীমা',
            'resources' => 'রিসোর্স ব্যবস্থাপনা',
            'nest' => 'Nest কনফিগারেশন',
            'docker' => 'Docker কনফিগারেশন',
            'startup' => 'স্টার্টআপ কনফিগারেশন',
            'variables' => 'সার্ভিস ভেরিয়েবল',
        ],

        'fields' => [
            'name' => [
                'label' => 'সার্ভারের নাম',
                'placeholder' => 'সার্ভারের নাম',
                'helper' => 'অক্ষরের সীমা: a-z A-Z 0-9 _ - . এবং স্পেস।',
            ],
            'owner' => [
                'label' => 'সার্ভার মালিক',
                'helper' => 'সার্ভার মালিকের ইমেইল ঠিকানা।',
            ],
            'description' => [
                'label' => 'সার্ভারের বিবরণ',
                'helper' => 'এই সার্ভারের সংক্ষিপ্ত বিবরণ।',
            ],
            'start_on_completion' => [
                'label' => 'ইনস্টল সম্পন্ন হলে সার্ভার চালু করুন',
            ],
            'node' => [
                'label' => 'নোড',
                'helper' => 'যে নোডে এই সার্ভার ডিপ্লয় করা হবে।',
            ],
            'allocation' => [
                'label' => 'ডিফল্ট অ্যালোকেশন',
                'helper' => 'এই সার্ভারের জন্য বরাদ্দকৃত প্রধান অ্যালোকেশন।',
            ],
            'additional_allocations' => [
                'label' => 'অতিরিক্ত অ্যালোকেশন',
                'helper' => 'তৈরির সময় এই সার্ভারে বরাদ্দ করার জন্য অতিরিক্ত অ্যালোকেশন।',
            ],
            'database_limit' => [
                'label' => 'ডাটাবেস সীমা',
                'helper' => 'এই সার্ভারের জন্য একজন ইউজার সর্বোচ্চ কতটি ডাটাবেস তৈরি করতে পারবে।',
            ],
            'allocation_limit' => [
                'label' => 'অ্যালোকেশন সীমা',
                'helper' => 'এই সার্ভারের জন্য একজন ইউজার সর্বোচ্চ কতটি অ্যালোকেশন তৈরি করতে পারবে।',
            ],
            'backup_limit' => [
                'label' => 'ব্যাকআপ সীমা',
                'helper' => 'এই সার্ভারের জন্য সর্বোচ্চ কতটি ব্যাকআপ তৈরি করা যাবে।',
            ],
            'cpu' => [
                'label' => 'CPU সীমা',
                'helper' => 'CPU সীমা না রাখতে 0 সেট করুন। একটি পূর্ণ ভার্চুয়াল কোর = 100%।',
            ],
            'threads' => [
                'label' => 'CPU পিনিং',
                'helper' => 'উন্নত: একটি সংখ্যা বা কমা দ্বারা আলাদা তালিকা ব্যবহার করুন, যেমন 0, 0-1,3, অথবা 0,1,3,4।',
            ],
            'memory' => [
                'label' => 'মেমোরি',
                'helper' => 'এই কনটেইনারের জন্য অনুমোদিত সর্বোচ্চ মেমোরি। সীমাহীন করতে 0 সেট করুন।',
            ],
            'swap' => [
                'label' => 'Swap',
                'helper' => 'Swap বন্ধ করতে 0, অথবা সীমাহীন Swap এর জন্য -1 ব্যবহার করুন।',
            ],
            'disk' => [
                'label' => 'ডিস্ক স্পেস',
                'helper' => 'সীমাহীন ডিস্ক ব্যবহারের জন্য 0 সেট করুন।',
            ],
            'io' => [
                'label' => 'Block IO Weight',
                'helper' => 'উন্নত: অন্যান্য চলমান কনটেইনারের তুলনায় IO পারফরম্যান্স। মান 10 থেকে 1000 এর মধ্যে হওয়া উচিত।',
            ],
            'oom_disabled' => [
                'label' => 'OOM Killer সক্রিয় করুন',
                'helper' => 'মেমোরি সীমা অতিক্রম করলে সার্ভার বন্ধ করে দেয়।',
            ],
            'nest' => [
                'label' => 'Nest',
                'helper' => 'এই সার্ভার যে Nest এর অধীনে থাকবে তা নির্বাচন করুন।',
            ],
            'egg' => [
                'label' => 'Egg',
                'helper' => 'এই সার্ভার কীভাবে কাজ করবে তা নির্ধারণকারী Egg নির্বাচন করুন।',
            ],
            'skip_scripts' => [
                'label' => 'Egg ইনস্টল স্ক্রিপ্ট বাদ দিন',
                'helper' => 'নির্বাচিত Egg এর সাথে ইনস্টল স্ক্রিপ্ট থাকলে এটি চেক না করা পর্যন্ত ইনস্টলের সময় স্ক্রিপ্ট চলবে।',
            ],
            'image' => [
                'label' => 'Docker ইমেজ',
                'helper' => 'ড্রপডাউন থেকে একটি ইমেজ নির্বাচন করুন, অথবা নিচে একটি কাস্টম ইমেজ লিখুন।',
            ],
            'custom_image' => [
                'label' => 'কাস্টম Docker ইমেজ',
                'placeholder' => 'অথবা একটি কাস্টম ইমেজ লিখুন...',
                'helper' => 'এই সার্ভার চালানোর জন্য ব্যবহৃত ডিফল্ট Docker ইমেজ।',
            ],
            'startup' => [
                'label' => 'স্টার্টআপ কমান্ড',
                'helper' => 'উপলব্ধ ভেরিয়েবল: {{SERVER_MEMORY}}, {{SERVER_IP}}, এবং {{SERVER_PORT}}।',
            ],
            'environment_placeholder' => [
                'label' => 'সার্ভিস ভেরিয়েবল কনফিগার করতে একটি egg নির্বাচন করুন',
            ],
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
        'unlimited' => 'Unlimited',
    ],

    'messages' => [
        'created' => 'Server has been successfully created.',
        'updated' => 'Server has been successfully updated.',
        'deleted' => 'Server has been successfully deleted.',
    ],

    'actions' => [
        'edit' => 'Edit',
        'random' => 'Random',
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
        'no_new_default_allocation' => 'You are attempting to delete the default allocation for this server but there is no fallback allocation to use.',
        'marked_as_failed' => 'This server was marked as having failed a previous installation. Current status cannot be toggled in this state.',
        'bad_variable' => 'There was a validation error with the :name variable.',
        'daemon_exception' => 'There was an exception while attempting to communicate with the daemon resulting in a HTTP/:code response code. This exception has been logged. (request id: :request_id)',
        'default_allocation_not_found' => 'The requested default allocation was not found in this server\'s allocations.',
    ],

    'alerts' => [
        'install_toggled' => 'Server install status has been toggled.',
        'server_suspended' => 'Server has been :action.',
        'server_reinstalled' => 'Server reinstall has been initiated.',
        'server_deleted' => 'Server has been deleted.',
        'server_delete_failed' => 'Failed to delete server.',
        'startup_changed' => 'The startup configuration for this server has been updated. If this server\'s nest or egg was changed a reinstall will be occurring now.',
        'server_created' => 'Server was successfully created on the panel. Please allow the daemon a few minutes to completely install this server.',
        'build_updated' => 'The build details for this server have been updated. Some changes may require a restart to take effect.',
        'suspension_toggled' => 'Server suspension status has been changed to :status.',
        'rebuild_on_boot' => 'This server has been marked as requiring a Docker Container rebuild. This will happen the next time the server is started.',
        'details_updated' => 'Server details have been successfully updated.',
        'docker_image_updated' => 'Successfully changed the default Docker image to use for this server. A reboot is required to apply this change.',
        'node_required' => 'You must have at least one node configured before you can add a server to this panel.',
        'transfer_nodes_required' => 'You must have at least two nodes configured before you can transfer servers.',
        'transfer_started' => 'Server transfer has been started.',
        'transfer_not_viable' => 'The node you selected does not have the required disk space or memory available to accommodate this server.',
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
