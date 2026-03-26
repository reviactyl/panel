<?php

namespace App\Models\Traits;

trait HasValidationRules
{
    public static function getRules(): array
    {
        return static::$validationRules;
    }
}
