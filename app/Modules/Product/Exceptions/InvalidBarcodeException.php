<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class InvalidBarcodeException extends DomainException
{
    public function __construct(string $message = 'Invalid barcode')
    {
        parent::__construct(message: $message, code: 422);
    }
}
