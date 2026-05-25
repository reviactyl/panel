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

    'accepted' => ':attribute গ্রহণ করতে হবে।',
    'active_url' => ':attribute একটি বৈধ URL নয়।',
    'after' => ':attribute অবশ্যই :date এর পরের একটি তারিখ হতে হবে।',
    'after_or_equal' => ':attribute অবশ্যই :date এর পর বা সমান একটি তারিখ হতে হবে।',
    'alpha' => ':attribute শুধুমাত্র অক্ষর ধারণ করতে পারে।',
    'alpha_dash' => ':attribute শুধুমাত্র অক্ষর, সংখ্যা এবং ড্যাশ ধারণ করতে পারে।',
    'alpha_num' => ':attribute শুধুমাত্র অক্ষর এবং সংখ্যা ধারণ করতে পারে।',
    'array' => ':attribute অবশ্যই একটি অ্যারে হতে হবে।',
    'before' => ':attribute অবশ্যই :date এর আগের একটি তারিখ হতে হবে।',
    'before_or_equal' => ':attribute অবশ্যই :date এর আগের বা সমান একটি তারিখ হতে হবে।',
    'between' => [
        'numeric' => ':attribute অবশ্যই :min এবং :max এর মধ্যে হতে হবে।',
        'file' => ':attribute অবশ্যই :min এবং :max কিলোবাইটের মধ্যে হতে হবে।',
        'string' => ':attribute অবশ্যই :min এবং :max অক্ষরের মধ্যে হতে হবে।',
        'array' => ':attribute এ অবশ্যই :min থেকে :max টি আইটেম থাকতে হবে।',
    ],
    'boolean' => ':attribute ফিল্ড অবশ্যই true অথবা false হতে হবে।',
    'confirmed' => ':attribute নিশ্চিতকরণ মেলেনি।',
    'date' => ':attribute একটি বৈধ তারিখ নয়।',
    'date_format' => ':attribute এর ফরম্যাট :format এর সাথে মেলেনি।',
    'different' => ':attribute এবং :other অবশ্যই ভিন্ন হতে হবে।',
    'digits' => ':attribute অবশ্যই :digits সংখ্যার হতে হবে।',
    'digits_between' => ':attribute অবশ্যই :min এবং :max সংখ্যার মধ্যে হতে হবে।',
    'dimensions' => ':attribute এর ছবির মাত্রা সঠিক নয়।',
    'distinct' => ':attribute ফিল্ডে ডুপ্লিকেট মান রয়েছে।',
    'email' => ':attribute অবশ্যই একটি বৈধ ইমেইল ঠিকানা হতে হবে।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',
    'file' => ':attribute অবশ্যই একটি ফাইল হতে হবে।',
    'filled' => ':attribute ফিল্ডটি আবশ্যক।',
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'in_array' => ':attribute ফিল্ডটি :other এ বিদ্যমান নেই।',
    'integer' => ':attribute অবশ্যই একটি পূর্ণ সংখ্যা হতে হবে।',
    'ip' => ':attribute অবশ্যই একটি বৈধ IP ঠিকানা হতে হবে।',
    'json' => ':attribute অবশ্যই একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'max' => [
        'numeric' => ':attribute :max এর বেশি হতে পারবে না।',
        'file' => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
        'array' => ':attribute এ :max টির বেশি আইটেম থাকতে পারবে না।',
    ],
    'mimes' => ':attribute অবশ্যই এই ধরনের ফাইল হতে হবে: :values।',
    'mimetypes' => ':attribute অবশ্যই এই ধরনের ফাইল হতে হবে: :values।',
    'min' => [
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
        'file' => ':attribute কমপক্ষে :min কিলোবাইট হতে হবে।',
        'string' => ':attribute কমপক্ষে :min অক্ষরের হতে হবে।',
        'array' => ':attribute এ কমপক্ষে :min টি আইটেম থাকতে হবে।',
    ],
    'not_in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'numeric' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'present' => ':attribute ফিল্ডটি উপস্থিত থাকতে হবে।',
    'regex' => ':attribute এর ফরম্যাট সঠিক নয়।',
    'required' => ':attribute ফিল্ডটি আবশ্যক।',
    'required_if' => ':other যদি :value হয় তাহলে :attribute ফিল্ডটি আবশ্যক।',
    'required_unless' => ':other যদি :values এর মধ্যে না থাকে তাহলে :attribute ফিল্ডটি আবশ্যক।',
    'required_with' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_with_all' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_without' => ':values উপস্থিত না থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_without_all' => ':values এর কোনোটিই উপস্থিত না থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'same' => ':attribute এবং :other অবশ্যই মিলতে হবে।',
    'size' => [
        'numeric' => ':attribute অবশ্যই :size হতে হবে।',
        'file' => ':attribute অবশ্যই :size কিলোবাইট হতে হবে।',
        'string' => ':attribute অবশ্যই :size অক্ষরের হতে হবে।',
        'array' => ':attribute এ অবশ্যই :size টি আইটেম থাকতে হবে।',
    ],
    'string' => ':attribute অবশ্যই একটি স্ট্রিং হতে হবে।',
    'timezone' => ':attribute অবশ্যই একটি বৈধ টাইমজোন হতে হবে।',
    'unique' => ':attribute ইতোমধ্যেই ব্যবহৃত হয়েছে।',
    'uploaded' => ':attribute আপলোড করা যায়নি।',
    'url' => ':attribute এর ফরম্যাট সঠিক নয়।',

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
        'variable_value' => ':env ভেরিয়েবল',
        'invalid_password' => 'এই অ্যাকাউন্টের জন্য দেওয়া পাসওয়ার্ডটি সঠিক নয়।',
    ],
];
