<?php

return [

    'label' => 'विस्तार',
    'plural-label' => 'एक्सटेंशन',

    'columns' => [
        'id' => 'ID',
        'name' => 'नाम',
        'version' => 'संस्करण',
        'author' => 'लेखक',
        'enabled' => 'सक्रिय',
        'updated' => 'अद्यतन',
        'manifest_json' => 'प्रकट JSON',
    ],

    'modals' => [
        'manifest' => 'विस्तार प्रकट',
    ],

    'actions' => [
        'edit' => 'संपादन करना',
        'upload' => 'अपलोड करें',
        'manifest' => 'प्रकट देखें',
        'disable' => 'अक्षम करना',
        'enable' => 'सक्षम',
        'delete' => 'मिटाना',
        'close' => 'बंद करना',
    ],

    'alerts' => [
        'enabled' => 'एक्सटेंशन सक्षम.',
        'enable_failed' => 'एक्सटेंशन सक्षम करने में विफल.',
        'disabled' => 'एक्सटेंशन अक्षम किया गया.',
        'disable_failed' => 'एक्सटेंशन अक्षम करने में विफल.',
        'uninstalled' => 'एक्सटेंशन अनइंस्टॉल किया गया.',
        'uninstall_failed' => 'एक्सटेंशन अनइंस्टॉल करने में विफल.',
        'could_not_locate_file' => 'अपलोड की गई पैकेज फ़ाइल का पता नहीं लगाया जा सका.',
        'invalid_file_type' => 'केवल .rext फ़ाइलों की अनुमति है।',
        'upload_hint' => 'केवल .rext एक्सटेंशन पैकेज की अनुमति है।',
        'install_failed' => 'एक्सटेंशन इंस्टॉल विफल रहा.',
        'install_success' => ':name (:version) सफलतापूर्वक स्थापित किया गया।',
    ],

];
