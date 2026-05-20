<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class DuplicateSKUException extends DomainException
{
    public function __construct(string $sku)
    {
        parent::__construct(
            message: "SKU already exists: {$sku}",
            code: 422,
        );
    }
}
