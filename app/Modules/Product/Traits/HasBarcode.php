<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use App\Modules\Product\Exceptions\InvalidBarcodeException;

trait HasBarcode
{
    public function validateBarcode(string $barcode, string $type): bool
    {
        return match ($type) {
            'ean13' => $this->validateEAN13($barcode),
            'upc' => preg_match('/^\d{12}$/', $barcode) === 1,
            'code128' => preg_match('/^[\x20-\x7E]+$/', $barcode) === 1,
            'qr' => true,
            default => throw new InvalidBarcodeException("Unknown barcode type: {$type}"),
        };
    }

    protected function validateEAN13(string $barcode): bool
    {
        if (preg_match('/^\d{13}$/', $barcode) !== 1) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $barcode[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checksum = (10 - ($sum % 10)) % 10;

        return $checksum === (int) $barcode[12];
    }
}
