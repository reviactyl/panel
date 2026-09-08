<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NodeFqdn implements Rule
{
    public function passes($attribute, $value): bool
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

    public function message(): string
    {
        return 'The :attribute must be a hostname or IP address without a scheme, port, or path.';
    }
}
