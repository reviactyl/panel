<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationRuleParser;

class EggVariableRules implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('validation.array')->translate();

            return;
        }

        foreach ($value as $rule) {
            if (! is_string($rule)) {
                $fail('validation.string')->translate();

                return;
            }
        }

        // Check the same pipe-delimited rules that will be stored and used by servers.
        // Run each rule separately so bail or a failed type check cannot hide a typo.
        foreach (explode('|', implode('|', $value)) as $rule) {
            [$name, $parameters] = ValidationRuleParser::parse($rule);
            if (in_array($name, ['Between', 'Min', 'Max', 'Size', 'Digits', 'DigitsBetween', 'MinDigits', 'MaxDigits', 'Decimal', 'MultipleOf'])) {
                foreach ($parameters as $parameter) {
                    if (! is_numeric($parameter)) {
                        $fail(trans('exceptions.nest.variables.bad_validation_rule', ['rule' => $rule]));

                        return;
                    }
                }
            }

            try {
                Validator::make(['value' => 'test'], ['value' => [$rule]])->fails();
            } catch (\BadMethodCallException|\InvalidArgumentException|\ErrorException|\Error|QueryException $exception) {
                $fail(trans('exceptions.nest.variables.bad_validation_rule', ['rule' => $rule]));

                return;
            }
        }
    }
}
