<?php

return [

    'label' => 'डेटाबेस',
    'plural-label' => 'डेटाबेस',

    'none' => 'कोई नहीं',

    'sections' => [
        'host_details' => [
            'title' => 'मेज़बान विवरण',
            'description' => 'डेटाबेस होस्ट कनेक्शन सेटिंग्स कॉन्फ़िगर करें।',
        ],

        'authentication' => [
            'title' => 'प्रमाणीकरण',
        ],

        'linked_node' => [
            'title' => 'लिंक किया गया नोड',
        ],
    ],

    'placeholders' => [
        'name' => 'उत्पादन MySQL',
    ],

    'helpers' => [
        'host' => 'डेटाबेस सर्वर का होस्टनाम या आईपी पता।',
        'linked_node' => 'वैकल्पिक। इस होस्ट को एक विशिष्ट नोड से लिंक करें।',
    ],

    'fields' => [
        'linked_node' => 'लिंक किया गया नोड',
    ],

    'columns' => [
        'id' => 'ID',
        'name' => 'नाम',
        'host' => 'मेज़बान',
        'port' => 'पत्तन',
        'username' => 'उपयोगकर्ता नाम',
        'linked_node' => 'लिंक किया गया नोड',
        'databases' => 'डेटाबेस',
        'created' => 'बनाया था',
    ],

    'actions' => [
        'edit' => 'संपादन करना',
        'delete' => 'मिटाना',
    ],

    'errors' => [
        'cannot_delete' => 'संबद्ध डेटाबेस के साथ डेटाबेस होस्ट को हटाया नहीं जा सकता।',
    ],

];
