<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum BarcodeTypeEnum: string
{
    case EAN13 = 'ean13';
    case UPC = 'upc';
    case Code128 = 'code128';
    case QR = 'qr';

    public function label(): string
    {
        return match ($this) {
            self::EAN13 => 'EAN-13',
            self::UPC => 'UPC',
            self::Code128 => 'Code 128',
            self::QR => 'QR Code',
        };
    }
}
