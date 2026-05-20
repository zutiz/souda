<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class VariantNotFoundException extends DomainException
{
    public function __construct(string $variantId)
    {
        parent::__construct(
            message: "Variant not found: {$variantId}",
            code: 404,
        );
    }
}
