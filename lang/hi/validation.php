<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute को स्वीकार किया जाना चाहिए।',
    'active_url' => ':attribute एक वैध URL नहीं है।',
    'after' => ':attribute :date के बाद की तारीख होनी चाहिए।',
    'after_or_equal' => ':attribute :date के बाद या उसके बराबर की तारीख होनी चाहिए।',
    'alpha' => ':attribute में केवल अक्षर हो सकते हैं।',
    'alpha_dash' => ':attribute में केवल अक्षर, संख्याएं और डैश हो सकते हैं।',
    'alpha_num' => ':attribute में केवल अक्षर और संख्याएं हो सकती हैं।',
    'array' => ':attribute एक array होना चाहिए।',
    'before' => ':attribute :date से पहले की तारीख होनी चाहिए।',
    'before_or_equal' => ':attribute :date से पहले या उसके बराबर की तारीख होनी चाहिए।',
    'between' => [
        'numeric' => ':attribute :min और :max के बीच होना चाहिए।',
        'file' => ':attribute :min और :max किलोबाइट के बीच होना चाहिए।',
        'string' => ':attribute :min और :max वर्णों के बीच होना चाहिए।',
        'array' => ':attribute में :min और :max आइटम होने चाहिए।',
    ],
    'boolean' => ':attribute फ़ील्ड सही या गलत होना चाहिए।',
    'confirmed' => ':attribute पुष्टिकरण मेल नहीं खाता।',
    'date' => ':attribute एक वैध तारीख नहीं है।',
    'date_format' => ':attribute प्रारूप :format से मेल नहीं खाता।',
    'different' => ':attribute और :other अलग होने चाहिए।',
    'digits' => ':attribute :digits अंक होने चाहिए।',
    'digits_between' => ':attribute :min और :max अंकों के बीच होना चाहिए।',
    'dimensions' => ':attribute में अमान्य छवि आयाम हैं।',
    'distinct' => ':attribute फ़ील्ड में डुप्लिकेट मान है।',
    'email' => ':attribute एक वैध ईमेल पता होना चाहिए।',
    'exists' => 'चयनित :attribute अमान्य है।',
    'file' => ':attribute एक फ़ाइल होनी चाहिए।',
    'filled' => ':attribute फ़ील्ड आवश्यक है।',
    'image' => ':attribute एक छवि होनी चाहिए।',
    'in' => 'चयनित :attribute अमान्य है।',
    'in_array' => ':attribute फ़ील्ड :other में मौजूद नहीं है।',
    'integer' => ':attribute एक पूर्णांक होना चाहिए।',
    'ip' => ':attribute एक वैध IP पता होना चाहिए।',
    'json' => ':attribute एक वैध JSON स्ट्रिंग होनी चाहिए।',
    'max' => [
        'numeric' => ':attribute :max से अधिक नहीं हो सकता।',
        'file' => ':attribute :max किलोबाइट से अधिक नहीं हो सकता।',
        'string' => ':attribute :max वर्णों से अधिक नहीं हो सकता।',
        'array' => ':attribute में :max से अधिक आइटम नहीं हो सकते।',
    ],
    'mimes' => ':attribute प्रकार की फ़ाइल होनी चाहिए: :values।',
    'mimetypes' => ':attribute प्रकार की फ़ाइल होनी चाहिए: :values।',
    'min' => [
        'numeric' => ':attribute कम से कम :min होना चाहिए।',
        'file' => ':attribute कम से कम :min किलोबाइट होना चाहिए।',
        'string' => ':attribute कम से कम :min वर्ण होने चाहिए।',
        'array' => ':attribute में कम से कम :min आइटम होने चाहिए।',
    ],
    'not_in' => 'चयनित :attribute अमान्य है।',
    'numeric' => ':attribute एक संख्या होनी चाहिए।',
    'present' => ':attribute फ़ील्ड मौजूद होना चाहिए।',
    'regex' => ':attribute प्रारूप अमान्य है।',
    'required' => ':attribute फ़ील्ड आवश्यक है।',
    'required_if' => ':attribute फ़ील्ड आवश्यक है जब :other :value हो।',
    'required_unless' => ':attribute फ़ील्ड आवश्यक है जब तक :other :values में न हो।',
    'required_with' => ':attribute फ़ील्ड आवश्यक है जब :values मौजूद हो।',
    'required_with_all' => ':attribute फ़ील्ड आवश्यक है जब :values मौजूद हो।',
    'required_without' => ':attribute फ़ील्ड आवश्यक है जब :values मौजूद नहीं हो।',
    'required_without_all' => ':attribute फ़ील्ड आवश्यक है जब :values में से कोई भी मौजूद नहीं हो।',
    'same' => ':attribute और :other मेल खाने चाहिए।',
    'size' => [
        'numeric' => ':attribute :size होना चाहिए।',
        'file' => ':attribute :size किलोबाइट होना चाहिए।',
        'string' => ':attribute :size वर्ण होने चाहिए।',
        'array' => ':attribute में :size आइटम होने चाहिए।',
    ],
    'string' => ':attribute एक स्ट्रिंग होनी चाहिए।',
    'timezone' => ':attribute एक वैध जोन होना चाहिए।',
    'unique' => ':attribute पहले से लिया जा चुका है।',
    'uploaded' => ':attribute अपलोड करने में विफल।',
    'url' => ':attribute प्रारूप अमान्य है।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

    // Internal validation logic for Reviactyl
    'internal' => [
        'variable_value' => ':env वेरिएबल',
        'invalid_password' => 'इस खाते के लिए प्रदान किया गया पासवर्ड अमान्य था।',
    ],
];
