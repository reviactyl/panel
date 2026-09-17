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

    'accepted' => ':attribute ಅನ್ನು ಒಪ್ಪಿಕೊಳ್ಳಬೇಕು.',
    'active_url' => ':attribute ಮಾನ್ಯವಾದ URL ಅಲ್ಲ.',
    'after' => ':attribute :date. ನಂತರದ ದಿನಾಂಕವಾಗಿರಬೇಕು',
    'after_or_equal' => ':attribute ನಂತರದ ದಿನಾಂಕವಾಗಿರಬೇಕು ಅಥವಾ :date. ಗೆ ಸಮನಾಗಿರಬೇಕು',
    'alpha' => ':attribute ಅಕ್ಷರಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.',
    'alpha_dash' => ':attribute ಅಕ್ಷರಗಳು, ಸಂಖ್ಯೆಗಳು ಮತ್ತು ಡ್ಯಾಶ್‌ಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.',
    'alpha_num' => ':attribute ಅಕ್ಷರಗಳು ಮತ್ತು ಸಂಖ್ಯೆಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.',
    'array' => ':attribute ಒಂದು ಶ್ರೇಣಿಯಾಗಿರಬೇಕು.',
    'before' => ':attribute :date. ಗಿಂತ ಹಿಂದಿನ ದಿನಾಂಕವಾಗಿರಬೇಕು',
    'before_or_equal' => ':attribute :date. ಗೆ ಮೊದಲು ಅಥವಾ ಅದಕ್ಕೆ ಸಮನಾದ ದಿನಾಂಕವಾಗಿರಬೇಕು',
    'between' => [
        'numeric' => ':attribute :min ಮತ್ತು :max. ನಡುವೆ ಇರಬೇಕು',
        'file' => ':attribute :min ಮತ್ತು :max ಕಿಲೋಬೈಟ್‌ಗಳ ನಡುವೆ ಇರಬೇಕು.',
        'string' => ':attribute :min ಮತ್ತು :max ಅಕ್ಷರಗಳ ನಡುವೆ ಇರಬೇಕು.',
        'array' => ':attribute :min ಮತ್ತು :max ಐಟಂಗಳನ್ನು ಹೊಂದಿರಬೇಕು.',
    ],
    'boolean' => ':attribute ಕ್ಷೇತ್ರವು ಸರಿ ಅಥವಾ ತಪ್ಪಾಗಿರಬೇಕು.',
    'confirmed' => ':attribute ದೃಢೀಕರಣವು ಹೊಂದಿಕೆಯಾಗುವುದಿಲ್ಲ.',
    'date' => ':attribute ಮಾನ್ಯವಾದ ದಿನಾಂಕವಲ್ಲ.',
    'date_format' => ':attribute ಸ್ವರೂಪವು :format. ಗೆ ಹೊಂದಿಕೆಯಾಗುವುದಿಲ್ಲ',
    'different' => ':attribute ಮತ್ತು :other ವಿಭಿನ್ನವಾಗಿರಬೇಕು.',
    'digits' => ':attribute :digits ಅಂಕೆಗಳಾಗಿರಬೇಕು.',
    'digits_between' => ':attribute :min ಮತ್ತು :max ಅಂಕಿಗಳ ನಡುವೆ ಇರಬೇಕು.',
    'dimensions' => ':attribute ಅಮಾನ್ಯವಾದ ಚಿತ್ರ ಆಯಾಮಗಳನ್ನು ಹೊಂದಿದೆ.',
    'distinct' => ':attribute ಕ್ಷೇತ್ರವು ನಕಲಿ ಮೌಲ್ಯವನ್ನು ಹೊಂದಿದೆ.',
    'email' => ':attribute ಮಾನ್ಯವಾದ ಇಮೇಲ್ ವಿಳಾಸವಾಗಿರಬೇಕು.',
    'exists' => 'ಆಯ್ಕೆಮಾಡಿದ :attribute ಅಮಾನ್ಯವಾಗಿದೆ.',
    'file' => ':attribute ಫೈಲ್ ಆಗಿರಬೇಕು.',
    'filled' => ':attribute ಕ್ಷೇತ್ರದ ಅಗತ್ಯವಿದೆ.',
    'image' => ':attribute ಒಂದು ಚಿತ್ರವಾಗಿರಬೇಕು.',
    'in' => 'ಆಯ್ಕೆಮಾಡಿದ :attribute ಅಮಾನ್ಯವಾಗಿದೆ.',
    'in_array' => ':attribute ಕ್ಷೇತ್ರವು :other. ನಲ್ಲಿ ಅಸ್ತಿತ್ವದಲ್ಲಿಲ್ಲ',
    'integer' => ':attribute ಪೂರ್ಣಾಂಕವಾಗಿರಬೇಕು.',
    'ip' => ':attribute ಮಾನ್ಯವಾದ IP ವಿಳಾಸವಾಗಿರಬೇಕು.',
    'json' => ':attribute ಮಾನ್ಯವಾದ JSON ಸ್ಟ್ರಿಂಗ್ ಆಗಿರಬೇಕು.',
    'max' => [
        'numeric' => ':attribute :max. ಗಿಂತ ಹೆಚ್ಚಿಲ್ಲದಿರಬಹುದು',
        'file' => ':attribute :max ಕಿಲೋಬೈಟ್‌ಗಳಿಗಿಂತ ಹೆಚ್ಚಿಲ್ಲದಿರಬಹುದು.',
        'string' => ':attribute :max ಅಕ್ಷರಗಳಿಗಿಂತ ಹೆಚ್ಚಿಲ್ಲದಿರಬಹುದು.',
        'array' => ':attribute :max ಗಿಂತ ಹೆಚ್ಚಿನ ಐಟಂಗಳನ್ನು ಹೊಂದಿಲ್ಲದಿರಬಹುದು.',
    ],
    'mimes' => ':attribute ಪ್ರಕಾರದ ಫೈಲ್ ಆಗಿರಬೇಕು: :values.',
    'mimetypes' => ':attribute ಪ್ರಕಾರದ ಫೈಲ್ ಆಗಿರಬೇಕು: :values.',
    'min' => [
        'numeric' => ':attribute ಕನಿಷ್ಠ :min. ಆಗಿರಬೇಕು',
        'file' => ':attribute ಕನಿಷ್ಠ :min ಕಿಲೋಬೈಟ್‌ಗಳಾಗಿರಬೇಕು.',
        'string' => ':attribute ಕನಿಷ್ಠ :min ಅಕ್ಷರಗಳಾಗಿರಬೇಕು.',
        'array' => ':attribute ಕನಿಷ್ಠ :min ಐಟಂಗಳನ್ನು ಹೊಂದಿರಬೇಕು.',
    ],
    'not_in' => 'ಆಯ್ಕೆಮಾಡಿದ :attribute ಅಮಾನ್ಯವಾಗಿದೆ.',
    'numeric' => ':attribute ಒಂದು ಸಂಖ್ಯೆಯಾಗಿರಬೇಕು.',
    'present' => ':attribute ಕ್ಷೇತ್ರವು ಇರಬೇಕು.',
    'regex' => ':attribute ಸ್ವರೂಪವು ಅಮಾನ್ಯವಾಗಿದೆ.',
    'required' => ':attribute ಕ್ಷೇತ್ರದ ಅಗತ್ಯವಿದೆ.',
    'required_if' => ':other :value. ಆಗಿರುವಾಗ :attribute ಕ್ಷೇತ್ರವು ಅಗತ್ಯವಿದೆ',
    'required_unless' => ':other :values. ನಲ್ಲಿ ಇಲ್ಲದಿದ್ದರೆ :attribute ಕ್ಷೇತ್ರದ ಅಗತ್ಯವಿದೆ',
    'required_with' => ':values ಇರುವಾಗ :attribute ಕ್ಷೇತ್ರ ಅಗತ್ಯವಿದೆ.',
    'required_with_all' => ':values ಇರುವಾಗ :attribute ಕ್ಷೇತ್ರ ಅಗತ್ಯವಿದೆ.',
    'required_without' => ':values ಇಲ್ಲದಿರುವಾಗ :attribute ಕ್ಷೇತ್ರವು ಅಗತ್ಯವಾಗಿರುತ್ತದೆ.',
    'required_without_all' => ':values ಯಾವುದೂ ಇಲ್ಲದಿರುವಾಗ :attribute ಕ್ಷೇತ್ರವು ಅಗತ್ಯವಾಗಿರುತ್ತದೆ.',
    'same' => ':attribute ಮತ್ತು :other ಹೊಂದಿಕೆಯಾಗಬೇಕು.',
    'size' => [
        'numeric' => ':attribute :size. ಆಗಿರಬೇಕು',
        'file' => ':attribute :size ಕಿಲೋಬೈಟ್‌ಗಳಾಗಿರಬೇಕು.',
        'string' => ':attribute :size ಅಕ್ಷರಗಳಾಗಿರಬೇಕು.',
        'array' => ':attribute :size ಐಟಂಗಳನ್ನು ಹೊಂದಿರಬೇಕು.',
    ],
    'string' => ':attribute ಒಂದು ಸ್ಟ್ರಿಂಗ್ ಆಗಿರಬೇಕು.',
    'timezone' => ':attribute ಮಾನ್ಯವಾದ ವಲಯವಾಗಿರಬೇಕು.',
    'unique' => ':attribute ಅನ್ನು ಈಗಾಗಲೇ ತೆಗೆದುಕೊಳ್ಳಲಾಗಿದೆ.',
    'uploaded' => ':attribute ಅಪ್‌ಲೋಡ್ ಮಾಡಲು ವಿಫಲವಾಗಿದೆ.',
    'url' => ':attribute ಸ್ವರೂಪವು ಅಮಾನ್ಯವಾಗಿದೆ.',

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
        'variable_value' => ':env ವೇರಿಯೇಬಲ್',
        'invalid_password' => 'ಈ ಖಾತೆಗೆ ಒದಗಿಸಲಾದ ಪಾಸ್‌ವರ್ಡ್ ಅಮಾನ್ಯವಾಗಿದೆ.',
    ],
];
