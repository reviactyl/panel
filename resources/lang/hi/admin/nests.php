<?php

return [

    'label' => 'Nest',
    'plural_label' => 'Nests',

    'sections' => [
        'configuration' => 'Nest Configuration',
    ],

    'fields' => [
        'name' => 'Name',
        'author' => 'Author',
        'description' => 'Description',
    ],

    'helpers' => [
        'name' => 'A unique name used to identify this nest.',
        'author' => 'The author of this nest. Must be a valid email.',
        'description' => 'A description of this nest.',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'author' => 'Author',
        'eggs' => 'Eggs',
        'servers' => 'Servers',
    ],

    'actions' => [
        'import' => 'Import Egg',
    ],

    'import' => [
        'file_label' => 'Egg File (JSON)',
        'nest_label' => 'Associated Nest',
        'file_not_found' => 'File not found',
        'file_not_found_body' => 'Could not locate uploaded file.',
        'invalid_format' => 'Invalid file format',
        'invalid_format_body' => 'Unexpected file format received.',
        'success' => 'Egg imported successfully',
        'failed' => 'Failed to import egg',
    ],

    'notices' => [
        'created' => 'एक नया नेस्ट, :name, सफलतापूर्वक बनाया गया है।',
        'deleted' => 'पैनल से अनुरोधित नेस्ट सफलतापूर्वक हटा दिया गया है।',
        'updated' => 'नेस्ट कॉन्फ़िगरेशन विकल्प सफलतापूर्वक अपडेट किए गए हैं।',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'इस Egg और इससे जुड़े वेरिएबल्स को सफलतापूर्वक आयात किया गया।',
            'updated_via_import' => 'इस Egg को दी गई फ़ाइल का उपयोग करके अपडेट किया गया है।',
            'deleted' => 'पैनल से अनुरोधित egg सफलतापूर्वक हटा दिया गया है।',
            'updated' => 'Egg कॉन्फ़िगरेशन सफलतापूर्वक अपडेट किया गया है।',
            'script_updated' => 'Egg इंस्टॉल स्क्रिप्ट अपडेट कर दी गई है और जब भी सर्वर इंस्टॉल होंगे तब चलेगी।',
            'egg_created' => 'एक नया egg सफलतापूर्वक बनाया गया। इस नए egg को लागू करने के लिए आपको किसी भी चल रहे डेमन को पुनः आरंभ करना होगा।',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'वेरिएबल ":variable" हटा दिया गया है और एक बार पुनर्निर्माण के बाद सर्वरों के लिए उपलब्ध नहीं होगा।',
            'variable_updated' => 'वेरिएबल ":variable" अपडेट किया गया है। परिवर्तन लागू करने के लिए आपको इस वेरिएबल का उपयोग करने वाले किसी भी सर्वर को पुनर्निर्माण करना होगा।',
            'variable_created' => 'नया वेरिएबल सफलतापूर्वक बनाया गया और इस egg को असाइन किया गया।',
        ],
    ],
];
