<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCardCvc implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' is required.');
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) < 3 || strlen($digits) > 4) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' must be 3 or 4 digits.');
        }
    }
}
