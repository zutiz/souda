<?php

declare(strict_types=1);

namespace App\Modules\Product\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSKU implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (preg_match('/^[A-Za-z0-9\-_]{3,100}$/', (string) $value) !== 1) {
            $fail('The :attribute must be 3-100 alphanumeric characters with dashes or underscores.');
        }
    }
}
