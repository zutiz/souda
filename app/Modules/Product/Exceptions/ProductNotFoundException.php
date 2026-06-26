<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class ProductNotFoundException extends DomainException
{
    public function __construct(string $productId)
    {
        parent::__construct(
            message: "Product not found: {$productId}",
            code: 404,
        );
    }
}
