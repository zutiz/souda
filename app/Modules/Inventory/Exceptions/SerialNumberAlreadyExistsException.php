<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class SerialNumberAlreadyExistsException extends DomainException
{
    public function __construct(string $serialNumber, string $productId)
    {
        parent::__construct(
            message: "Serial number '{$serialNumber}' already exists for product {$productId}",
            code: 409,
        );
    }
}
