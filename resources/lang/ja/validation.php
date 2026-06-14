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

    'accepted' => ':attribute を承認する必要があります。',
    'active_url' => ':attribute は有効な URL ではありません。',
    'after' => ':attribute は :date より後の日付である必要があります。',
    'after_or_equal' => ':attribute は :date 以降の日付である必要があります。',
    'alpha' => ':attribute は英字のみ使用できます。',
    'alpha_dash' => ':attribute は英字、数字、ダッシュのみ使用できます。',
    'alpha_num' => ':attribute は英字と数字のみ使用できます。',
    'array' => ':attribute は配列である必要があります。',
    'before' => ':attribute は :date より前の日付である必要があります。',
    'before_or_equal' => ':attribute は :date 以前の日付である必要があります。',
    'between' => [
        'numeric' => ':attribute は :min から :max の間である必要があります。',
        'file' => ':attribute は :min から :max キロバイトの間である必要があります。',
        'string' => ':attribute は :min から :max 文字の間である必要があります。',
        'array' => ':attribute は :min から :max 個の項目を持つ必要があります。',
    ],
    'boolean' => ':attribute フィールドは true または false である必要があります。',
    'confirmed' => ':attribute の確認が一致しません。',
    'date' => ':attribute は有効な日付ではありません。',
    'date_format' => ':attribute はフォーマット :format に一致しません。',
    'different' => ':attribute と :other は異なる必要があります。',
    'digits' => ':attribute は :digits 桁である必要があります。',
    'digits_between' => ':attribute は :min から :max 桁の間である必要があります。',
    'dimensions' => ':attribute の画像サイズが無効です。',
    'distinct' => ':attribute フィールドに重複した値があります。',
    'email' => ':attribute は有効なメールアドレスである必要があります。',
    'exists' => '選択された :attribute は無効です。',
    'file' => ':attribute はファイルである必要があります。',
    'filled' => ':attribute フィールドは必須です。',
    'image' => ':attribute は画像である必要があります。',
    'in' => '選択された :attribute は無効です。',
    'in_array' => ':attribute フィールドは :other に存在しません。',
    'integer' => ':attribute は整数である必要があります。',
    'ip' => ':attribute は有効な IP アドレスである必要があります。',
    'json' => ':attribute は有効な JSON 文字列である必要があります。',
    'max' => [
        'numeric' => ':attribute は :max を超えることはできません。',
        'file' => ':attribute は :max キロバイトを超えることはできません。',
        'string' => ':attribute は :max 文字を超えることはできません。',
        'array' => ':attribute は :max 個を超えることはできません。',
    ],
    'mimes' => ':attribute は次のタイプのファイルである必要があります: :values',
    'mimetypes' => ':attribute は次のタイプのファイルである必要があります: :values',
    'min' => [
        'numeric' => ':attribute は少なくとも :min である必要があります。',
        'file' => ':attribute は少なくとも :min キロバイトである必要があります。',
        'string' => ':attribute は少なくとも :min 文字である必要があります。',
        'array' => ':attribute は少なくとも :min 個の項目を持つ必要があります。',
    ],
    'not_in' => '選択された :attribute は無効です。',
    'numeric' => ':attribute は数値である必要があります。',
    'present' => ':attribute フィールドが存在する必要があります。',
    'regex' => ':attribute のフォーマットが無効です。',
    'required' => ':attribute フィールドは必須です。',
    'required_if' => ':other が :value の場合、:attribute フィールドは必須です。',
    'required_unless' => ':other が :values に含まれない限り、:attribute フィールドは必須です。',
    'required_with' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_with_all' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_without' => ':values が存在しない場合、:attribute フィールドは必須です。',
    'required_without_all' => ':values がいずれも存在しない場合、:attribute フィールドは必須です。',
    'same' => ':attribute と :other は一致する必要があります。',
    'size' => [
        'numeric' => ':attribute は :size である必要があります。',
        'file' => ':attribute は :size キロバイトである必要があります。',
        'string' => ':attribute は :size 文字である必要があります。',
        'array' => ':attribute は :size 個の項目を含む必要があります。',
    ],
    'string' => ':attribute は文字列である必要があります。',
    'timezone' => ':attribute は有効なタイムゾーンである必要があります。',
    'unique' => ':attribute はすでに使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'url' => ':attribute のフォーマットが無効です。',

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
        'variable_value' => ':env 変数',
        'invalid_password' => 'このアカウントのパスワードが無効です。',
    ],
];
