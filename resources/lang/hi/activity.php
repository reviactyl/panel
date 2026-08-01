<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'entries' => [
        'system-user' => 'सिस्टम उपयोगकर्ता',
        'system' => 'सिस्टम',
        'using-api-key' => 'API कुंजी का उपयोग कर रहा है',
        'using-sftp' => 'SFTP का उपयोग कर रहा है',
    ],
    'auth' => [
        'fail' => 'लॉगिन असफल',
        'success' => 'लॉगिन हुआ',
        'password-reset' => 'पासवर्ड रीसेट किया गया',
        'reset-password' => 'पासवर्ड रीसेट का अनुरोध किया गया',
        'checkpoint' => 'दो-फैक्टर प्रमाणीकरण का अनुरोध किया गया',
        'recovery-token' => 'दो-फैक्टर रिकवरी टोकन का उपयोग किया गया',
        'token' => 'दो-फैक्टर चुनौती हल की गई',
        'ip-blocked' => 'असूचीबद्ध IP पता :identifier से अनुरोध अवरुद्ध किया गया',
        'sftp' => [
            'fail' => 'SFTP लॉगिन असफल',
        ],
    ],
    'user' => [
        'user' => [
            'create' => 'Created a new user :email',
        ],
        'account' => [
            'email-changed' => 'ईमेल :old से :new में बदला गया',
            'password-changed' => 'पासवर्ड बदल दिया गया',
            'language-changed' => 'भाषा को :old से :new में बदला गया',
        ],
        'api-key' => [
            'create' => 'नई API कुंजी :identifier बनाई गई',
            'delete' => 'API कुंजी :identifier हटाई गई',
        ],
        'ssh-key' => [
            'create' => 'SSH कुंजी :fingerprint खाते में जोड़ी गई',
            'delete' => 'SSH कुंजी :fingerprint खाते से हटाई गई',
        ],
        'two-factor' => [
            'create' => 'दो-फैक्टर प्रमाणीकरण सक्षम किया गया',
            'delete' => 'दो-फैक्टर प्रमाणीकरण अक्षम किया गया',
        ],
    ],
    'server' => [
        'reinstall' => 'सर्वर पुनः स्थापित किया गया',
        'console' => [
            'command' => 'सर्वर पर ":command" निष्पादित किया गया',
        ],
        'power' => [
            'start' => 'सर्वर शुरू किया गया',
            'stop' => 'सर्वर बंद किया गया',
            'restart' => 'सर्वर पुनः शुरू किया गया',
            'kill' => 'सर्वर प्रक्रिया समाप्त की गई',
        ],
        'backup' => [
            'download' => ':name बैकअप डाउनलोड किया गया',
            'delete' => ':name बैकअप हटा दिया गया',
            'restore' => ':name बैकअप पुनर्स्थापित किया गया (हटाई गई फ़ाइलें: :truncate)',
            'restore-complete' => ':name बैकअप की पुनर्स्थापना पूरी हुई',
            'restore-failed' => ':name बैकअप की पुनर्स्थापना विफल रही',
            'start' => 'नया बैकअप :name शुरू किया गया',
            'complete' => ':name बैकअप को पूरा के रूप में चिह्नित किया गया',
            'fail' => ':name बैकअप को असफल के रूप में चिह्नित किया गया',
            'lock' => ':name बैकअप लॉक किया गया',
            'unlock' => ':name बैकअप अनलॉक किया गया',
        ],
        'database' => [
            'create' => 'नया डेटाबेस :name बनाया गया',
            'rotate-password' => 'डेटाबेस :name के लिए पासवर्ड बदल दिया गया',
            'delete' => 'डेटाबेस :name हटा दिया गया',
        ],
        'file' => [
            'compress_one' => ':directory:file संकुचित किया गया',
            'compress_other' => ':directory में :count फ़ाइलें संकुचित की गईं',
            'read' => ':file की सामग्री देखी गई',
            'copy' => ':file की कॉपी बनाई गई',
            'create-directory' => 'निर्देशिका :directory:name बनाई गई',
            'decompress' => ':directory में :files अनसंकुचित किए गए',
            'delete_one' => ':directory:files.0 हटा दिया गया',
            'delete_other' => ':directory में :count फ़ाइलें हटा दी गईं',
            'download' => ':file डाउनलोड किया गया',
            'pull' => ':url से :directory में रिमोट फ़ाइल डाउनलोड की गई',
            'rename_one' => ':directory:files.0.from को :directory:files.0.to में नाम बदल दिया गया',
            'rename_other' => ':directory में :count फ़ाइलों का नाम बदल दिया गया',
            'write' => ':file में नई सामग्री लिखी गई',
            'upload' => 'फ़ाइल अपलोड शुरू किया गया',
            'uploaded' => ':directory:file अपलोड किया गया',
        ],
        'sftp' => [
            'denied' => 'अनुमतियों के कारण SFTP एक्सेस अवरुद्ध',
            'create_one' => ':files.0 बनाया गया',
            'create_other' => ':count नई फ़ाइलें बनाई गईं',
            'write_one' => ':files.0 की सामग्री संशोधित की गई',
            'write_other' => ':count फ़ाइलों की सामग्री संशोधित की गई',
            'delete_one' => ':files.0 हटा दिया गया',
            'delete_other' => ':count फ़ाइलें हटा दी गईं',
            'create-directory_one' => ':files.0 निर्देशिका बनाई गई',
            'create-directory_other' => ':count निर्देशिकाएँ बनाई गईं',
            'rename_one' => ':files.0.from को :files.0.to में नाम बदल दिया गया',
            'rename_other' => ':count फ़ाइलों का नाम बदला या स्थानांतरित किया गया',
        ],
        'allocation' => [
            'create' => ':allocation सर्वर में जोड़ा गया',
            'notes' => ':allocation के नोट्स ":old" से ":new" में अपडेट किए गए',
            'primary' => ':allocation को प्राथमिक सर्वर एलोकेशन के रूप में सेट किया गया',
            'delete' => ':allocation एलोकेशन हटा दिया गया',
        ],
        'schedule' => [
            'create' => ':name अनुसूची बनाई गई',
            'update' => ':name अनुसूची अपडेट की गई',
            'execute' => ':name अनुसूची को मैन्युअल रूप से निष्पादित किया गया',
            'delete' => ':name अनुसूची हटा दी गई',
        ],
        'task' => [
            'create' => ':name अनुसूची के लिए नया ":action" कार्य बनाया गया',
            'update' => ':name अनुसूची के लिए ":action" कार्य अपडेट किया गया',
            'delete' => ':name अनुसूची के लिए एक कार्य हटा दिया गया',
        ],
        'settings' => [
            'rename' => 'सर्वर का नाम :old से :new में बदल दिया गया',
            'description' => 'सर्वर विवरण :old से :new में बदल दिया गया',
        ],
        'startup' => [
            'edit' => ':variable वेरिएबल ":old" से ":new" में बदल दिया गया',
            'image' => 'सर्वर के लिए Docker इमेज :old से :new में अपडेट की गई',
        ],
        'subuser' => [
            'create' => ':email को एक उप-उपयोगकर्ता के रूप में जोड़ा गया',
            'update' => ':email के उप-उपयोगकर्ता अनुमतियाँ अपडेट की गईं',
            'delete' => ':email को उप-उपयोगकर्ता के रूप में हटाया गया',
        ],
    ],
];
