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

    'accepted' => ':attribute måste accepteras.',
    'active_url' => ':attribute är inte en giltig URL.',
    'after' => ':attribute måste vara ett datum efter :date.',
    'after_or_equal' => ':attribute måste vara ett datum efter eller lika med :date.',
    'alpha' => ':attribute får endast innehålla bokstäver.',
    'alpha_dash' => ':attribute får endast innehålla bokstäver, siffror och bindestreck.',
    'alpha_num' => ':attribute får endast innehålla bokstäver och siffror.',
    'array' => ':attribute måste vara en array.',
    'before' => ':attribute måste vara ett datum före :date.',
    'before_or_equal' => ':attribute måste vara ett datum före eller lika med :date.',
    'between' => [
        'numeric' => ':attribute måste vara mellan :min och :max.',
        'file' => ':attribute måste vara mellan :min och :max kilobyte.',
        'string' => ':attribute måste vara mellan :min och :max tecken.',
        'array' => ':attribute måste ha mellan :min och :max objekt.',
    ],
    'boolean' => ':attribute-fältet måste vara sant eller falskt.',
    'confirmed' => ':attribute-bekräftelsen matchar inte.',
    'date' => ':attribute är inte ett giltigt datum.',
    'date_format' => ':attribute matchar inte formatet :format.',
    'different' => ':attribute och :other måste vara olika.',
    'digits' => ':attribute måste vara :digits siffror.',
    'digits_between' => ':attribute måste vara mellan :min och :max siffror.',
    'dimensions' => ':attribute har ogiltiga bilddimensioner.',
    'distinct' => ':attribute-fältet har ett duplicerat värde.',
    'email' => ':attribute måste vara en giltig e-postadress.',
    'exists' => 'Vald :attribute är ogiltig.',
    'file' => ':attribute måste vara en fil.',
    'filled' => ':attribute-fältet är obligatoriskt.',
    'image' => ':attribute måste vara en bild.',
    'in' => 'Vald :attribute är ogiltig.',
    'in_array' => ':attribute-fältet finns inte i :other.',
    'integer' => ':attribute måste vara ett heltal.',
    'ip' => ':attribute måste vara en giltig IP-adress.',
    'json' => ':attribute måste vara en giltig JSON-sträng.',
    'max' => [
        'numeric' => ':attribute får inte vara större än :max.',
        'file' => ':attribute får inte vara större än :max kilobyte.',
        'string' => ':attribute får inte vara större än :max tecken.',
        'array' => ':attribute får inte ha fler än :max objekt.',
    ],
    'mimes' => ':attribute måste vara en fil av typen: :values.',
    'mimetypes' => ':attribute måste vara en fil av typen: :values.',
    'min' => [
        'numeric' => ':attribute måste vara minst :min.',
        'file' => ':attribute måste vara minst :min kilobyte.',
        'string' => ':attribute måste vara minst :min tecken.',
        'array' => ':attribute måste ha minst :min objekt.',
    ],
    'not_in' => 'Vald :attribute är ogiltig.',
    'numeric' => ':attribute måste vara ett nummer.',
    'present' => ':attribute-fältet måste finnas.',
    'regex' => ':attribute-formatet är ogiltigt.',
    'required' => ':attribute-fältet är obligatoriskt.',
    'required_if' => ':attribute-fältet är obligatoriskt när :other är :value.',
    'required_unless' => ':attribute-fältet är obligatoriskt om inte :other finns i :values.',
    'required_with' => ':attribute-fältet är obligatoriskt när :values finns.',
    'required_with_all' => ':attribute-fältet är obligatoriskt när :values finns.',
    'required_without' => ':attribute-fältet är obligatoriskt när :values inte finns.',
    'required_without_all' => ':attribute-fältet är obligatoriskt när ingen av :values finns.',
    'same' => ':attribute och :other måste matcha.',
    'size' => [
        'numeric' => ':attribute måste vara :size.',
        'file' => ':attribute måste vara :size kilobyte.',
        'string' => ':attribute måste vara :size tecken.',
        'array' => ':attribute måste innehålla :size objekt.',
    ],
    'string' => ':attribute måste vara en sträng.',
    'timezone' => ':attribute måste vara en giltig zon.',
    'unique' => ':attribute har redan tagits.',
    'uploaded' => ':attribute misslyckades att laddas upp.',
    'url' => ':attribute-formatet är ogiltigt.',

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
        'variable_value' => ':env-variabel',
        'invalid_password' => 'Lösenordet som angavs var ogiltigt för detta konto.',
    ],
];
