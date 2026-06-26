<?php

declare(strict_types=1);

namespace App\Modules\Product\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBarcode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $barcode = (string) $value;
        $type = request()->input('barcode_type');

        if ($type === 'ean13') {
            if (preg_match('/^\d{13}$/', $barcode) !== 1) {
                $fail('The :attribute must be a valid EAN-13 barcode.');

                return;
            }

            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int) $barcode[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checksum = (10 - ($sum % 10)) % 10;

            if ($checksum !== (int) $barcode[12]) {
                $fail('The :attribute has an invalid checksum.');
            }
        } elseif ($type === 'upc') {
            if (preg_match('/^\d{12}$/', $barcode) !== 1) {
                $fail('The :attribute must be a valid UPC barcode (12 digits).');
            }
        } elseif ($type === 'code128') {
            if (preg_match('/^[\x20-\x7E]+$/', $barcode) !== 1) {
                $fail('The :attribute must be a valid Code 128 barcode.');
            }
        }
    }
}
