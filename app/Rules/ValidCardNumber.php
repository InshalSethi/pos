<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCardNumber implements ValidationRule
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

        if (strlen($digits) < 13 || strlen($digits) > 19) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' must be between 13 and 19 digits.');
            return;
        }

        // Luhn Algorithm Validation
        $sum = 0;
        $shouldDouble = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $digit = (int) $digits[$i];
            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        if ($sum % 10 !== 0) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' is not a valid credit card number.');
        }
    }
}
