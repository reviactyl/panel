<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NodeFqdn implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValidHost($value)) {
            $fail('The :attribute must be a hostname or IP address without a scheme, port, or path.');
        }
    }

    private function isValidHost(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return true;
        }

        // Brackets are valid around an IPv6 host in a connection address.
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            return (bool) filter_var(substr($value, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        return (bool) filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }
}
