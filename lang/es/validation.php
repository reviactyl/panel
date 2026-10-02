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

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo puede contener letras.',
    'alpha_dash' => 'El campo :attribute solo puede contener letras, números y guiones.',
    'alpha_num' => 'El campo :attribute solo puede contener letras y números.',
    'array' => 'El campo :attribute debe ser un array.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El campo :attribute debe tener entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_format' => 'El campo :attribute no coincide con el formato :format.',
    'different' => 'El campo :attribute y :other deben ser diferentes.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute tiene dimensiones de imagen inválidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'exists' => 'El :attribute seleccionado es inválido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute es requerido.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado es inválido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => 'El campo :attribute debe ser un entero.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'json' => 'El campo :attribute debe ser una cadena JSON válida.',
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'file' => 'El campo :attribute no puede ser mayor que :max kilobytes.',
        'string' => 'El campo :attribute no puede ser mayor que :max caracteres.',
        'array' => 'El campo :attribute no puede tener más de :max elementos.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El campo :attribute debe tener al menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in' => 'El :attribute seleccionado es inválido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'present' => 'El campo :attribute debe estar presente.',
    'regex' => 'El formato de :attribute es inválido.',
    'required' => 'El campo :attribute es requerido.',
    'required_if' => 'El campo :attribute es requerido cuando :other es :value.',
    'required_unless' => 'El campo :attribute es requerido a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es requerido cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es requerido cuando :values está presente.',
    'required_without' => 'El campo :attribute es requerido cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es requerido cuando ninguno de :values están presentes.',
    'same' => 'El campo :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file' => 'El campo :attribute debe tener :size kilobytes.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
        'array' => 'El campo :attribute debe contener :size elementos.',
    ],
    'string' => 'El campo :attribute debe ser una cadena.',
    'timezone' => 'El campo :attribute debe ser una zona válida.',
    'unique' => 'El :attribute ya ha sido tomado.',
    'uploaded' => 'El :attribute falló al subirse.',
    'url' => 'El formato de :attribute es inválido.',

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
        'variable_value' => 'variable :env',
        'invalid_password' => 'La contraseña proporcionada es inválida para esta cuenta.',
    ],
];
