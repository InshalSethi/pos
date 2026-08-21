<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidCardExpiry implements ValidationRule
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

        $clean = preg_replace('/\s/', '', (string) $value);

        if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $clean, $matches)) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' must be in MM/YY format.');
            return;
        }

        $month = (int) $matches[1];
        $year = 2000 + (int) $matches[2];

        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('m');

        if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
            $fail('The credit card has expired.');
        }
    }
}
