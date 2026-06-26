<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class WarehouseNotFoundException extends DomainException
{
    public function __construct(int $warehouseId)
    {
        parent::__construct(
            message: "Warehouse not found: {$warehouseId}",
            code: 404,
        );
    }
}
