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

    'accepted' => 'Поле :attribute должно быть принято.',
    'active_url' => 'Поле :attribute содержит недействительный URL.',
    'after' => 'Поле :attribute должно содержать дату после :date.',
    'after_or_equal' => 'Поле :attribute должно содержать дату после или равную :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры и дефисы.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'Поле :attribute должно содержать дату до :date.',
    'before_or_equal' => 'Поле :attribute должно содержать дату до или равную :date.',
    'between' => [
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'file' => 'Размер файла :attribute должен быть от :min до :max килобайт.',
        'string' => 'Количество символов в поле :attribute должно быть от :min до :max.',
        'array' => 'Количество элементов в поле :attribute должно быть от :min до :max.',
    ],
    'boolean' => 'Поле :attribute должно иметь значение истина или ложь.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'date' => 'Поле :attribute содержит недействительную дату.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'different' => 'Поля :attribute и :other должны отличаться.',
    'digits' => 'Поле :attribute должно содержать :digits цифр.',
    'digits_between' => 'Количество цифр в поле :attribute должно быть от :min до :max.',
    'dimensions' => 'Изображение :attribute имеет недопустимые размеры.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'email' => 'Поле :attribute должно содержать действительный адрес электронной почты.',
    'exists' => 'Выбранное значение поля :attribute недействительно.',
    'file' => 'Поле :attribute должно содержать файл.',
    'filled' => 'Поле :attribute обязательно для заполнения.',
    'image' => 'Поле :attribute должно содержать изображение.',
    'in' => 'Выбранное значение поля :attribute недействительно.',
    'in_array' => 'Поле :attribute отсутствует в :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно содержать действительный IP-адрес.',
    'json' => 'Поле :attribute должно содержать корректную JSON-строку.',
    'max' => [
        'numeric' => 'Поле :attribute не может быть больше :max.',
        'file' => 'Размер файла :attribute не может превышать :max килобайт.',
        'string' => 'Количество символов в поле :attribute не может превышать :max.',
        'array' => 'Количество элементов в поле :attribute не может превышать :max.',
    ],
    'mimes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'mimetypes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'min' => [
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'file' => 'Размер файла :attribute должен быть не меньше :min килобайт.',
        'string' => 'Количество символов в поле :attribute должно быть не меньше :min.',
        'array' => 'Количество элементов в поле :attribute должно быть не меньше :min.',
    ],
    'not_in' => 'Выбранное значение поля :attribute недействительно.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'present' => 'Поле :attribute должно присутствовать.',
    'regex' => 'Поле :attribute имеет недопустимый формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_if' => 'Поле :attribute обязательно для заполнения, когда :other равно :value.',
    'required_unless' => 'Поле :attribute обязательно для заполнения, если :other не входит в :values.',
    'required_with' => 'Поле :attribute обязательно для заполнения, когда присутствует :values.',
    'required_with_all' => 'Поле :attribute обязательно для заполнения, когда присутствует :values.',
    'required_without' => 'Поле :attribute обязательно для заполнения, когда :values отсутствует.',
    'required_without_all' => 'Поле :attribute обязательно для заполнения, когда ни одно из значений :values не присутствует.',
    'same' => 'Поля :attribute и :other должны совпадать.',
    'size' => [
        'numeric' => 'Поле :attribute должно быть равно :size.',
        'file' => 'Размер файла :attribute должен составлять :size килобайт.',
        'string' => 'Количество символов в поле :attribute должно быть равно :size.',
        'array' => 'Количество элементов в поле :attribute должно быть равно :size.',
    ],
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно содержать действительный часовой пояс.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить :attribute.',
    'url' => 'Поле :attribute имеет недопустимый формат.',

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
        'variable_value' => 'Переменная :env',
        'invalid_password' => 'Указанный пароль недействителен для этой учётной записи.',
    ],
];
