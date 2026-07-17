<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class InsufficientStockException extends DomainException
{
    public function __construct(
        string $message = 'Insufficient stock available',
        int $code = 422,
    ) {
        parent::__construct(message: $message, code: $code);
    }

    public static function forProduct(string $productId, int $requested, int $available): self
    {
        return new self(
            message: "Insufficient stock for product {$productId}: requested {$requested}, available {$available}",
        );
    }

    public static function forVariant(string $variantId, int $requested, int $available): self
    {
        return new self(
            message: "Insufficient stock for variant {$variantId}: requested {$requested}, available {$available}",
        );
    }
}
