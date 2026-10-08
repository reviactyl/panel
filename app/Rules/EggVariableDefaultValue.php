<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class EggVariableDefaultValue implements ValidationRule
{
    public bool $implicit = true;

    public function __construct(private mixed $rules) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Invalid rule definitions are reported by the rules field itself.
        if (Validator::make(['rules' => $this->rules], ['rules' => [new EggVariableRules()]])->fails()) {
            return;
        }

        $validator = Validator::make(
            ['default_value' => $value],
            ['default_value' => implode('|', $this->rules)],
            [],
            ['default_value' => trans('admin/eggs.fields.default_value')]
        );

        foreach ($validator->errors()->get('default_value') as $message) {
            $fail($message);
        }
    }
}
