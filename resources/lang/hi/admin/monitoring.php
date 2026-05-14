<?php

return [
    'navigation' => [
        'label' => 'निगरानी',
        'group' => 'प्रशासन',
    ],

    'page' => [
        'title' => 'निगरानी',
        'heading' => 'लाइव मॉनिटरिंग',
    ],

    'actions' => [
        'refresh' => 'डेटा ताज़ा करें',
    ],

    'selector' => [
        'label' => 'नोड का चयन करें',
        'placeholder' => 'एक नोड चुनें...',
    ],

    'stats' => [
        'cpu_usage' => 'सीपीयू उपयोग',
        'cpu_cores' => ':count कोर उपलब्ध हैं',
        'memory_usage' => 'स्मृति प्रयोग',
        'disk_usage' => 'डिस्क उपयोग',
        'network_traffic' => 'नेटवर्क ट्रैफ़िक',
        'uptime' => 'अपटाइम',
        'last_updated' => 'आखरी अपडेट',
        'no_node' => 'कोई नोड चयनित नहीं',
        'no_node_desc' => 'मॉनिटरिंग डेटा देखने के लिए कृपया एक नोड चुनें',
        'no_node_hint' => 'ऊपर दिए गए ड्रॉपडाउन का उपयोग करें',
        'error' => 'गलती',
        'error_desc' => 'निगरानी डेटा लोड करने में असमर्थ',
        'error_fetch' => 'एजेंट से डेटा लाने में असमर्थ',
        'error_node_gone' => 'नोड अब मौजूद नहीं है',
    ],

    'details' => [
        'heading' => 'सिस्टम विवरण',
        'button' => 'विवरण',
        'close' => 'बंद करना',
        'no_data' => 'कोई डेटा मौजूद नहीं। सुनिश्चित करें कि नोड ऑनलाइन है.',

        'cpu_section' => 'CPU',
        'cpu_total' => 'कुल उपयोग',
        'cpu_cores' => 'कोर',
        'per_core' => 'प्रति-कोर उपयोग',

        'memory_section' => 'याद',
        'total_memory' => 'कुल',
        'used_memory' => 'इस्तेमाल किया गया',
        'free_memory' => 'मुक्त',
        'available_memory' => 'उपलब्ध',

        'swap_section' => 'बदलना',
        'swap_none' => 'इस नोड पर कोई स्वैप कॉन्फ़िगर नहीं किया गया है.',
        'swap_total' => 'कुल',
        'swap_used' => 'इस्तेमाल किया गया',
        'swap_free' => 'मुक्त',
        'swap_usage' => 'प्रयोग',

        'network_section' => 'नेटवर्क',
        'bytes_sent' => 'बाइट्स भेजे गए',
        'bytes_recv' => 'बाइट्स प्राप्त हुए',
        'packets_sent' => 'पैकेट भेजे गए',
        'packets_received' => 'पैकेट प्राप्त हुए',

        'runtime_section' => 'क्रम',
        'go_version' => 'जाओ संस्करण',
        'arch' => 'वास्तुकला',
        'goroutines' => 'गोरौटाइन्स',
        'uptime' => 'अपटाइम',
    ],
    'servers' => [
        'heading' => 'सर्वर उपयोग',
        'no_node' => 'सर्वर उपयोग देखने के लिए एक नोड चुनें।',
        'no_servers' => 'इस नोड पर कोई सर्वर नहीं मिला.',
        'error_fetch' => 'एजेंट से सर्वर डेटा लाने में असमर्थ.',
        'col' => [
            'name' => 'सर्वर',
            'state' => 'राज्य',
            'cpu' => 'CPU',
            'memory' => 'याद',
            'disk' => 'डिस्क',
            'network' => 'नेटवर्क',
            'uptime' => 'अपटाइम',
        ],
        'states' => [
            'running' => 'दौड़ना',
            'starting' => 'प्रारंभ',
            'stopping' => 'रोक',
            'offline' => 'ऑफलाइन',
            'crashed' => 'दुर्घटनाग्रस्त',
            'unknown' => 'अज्ञात',
        ],
    ],
];
