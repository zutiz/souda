<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class BatchNotFoundException extends DomainException
{
    public function __construct(string $batchNumber, string $productId)
    {
        parent::__construct(
            message: "Batch not found: {$batchNumber} for product {$productId}",
            code: 404,
        );
    }
}
