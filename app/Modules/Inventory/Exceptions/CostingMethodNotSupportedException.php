<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class CostingMethodNotSupportedException extends DomainException
{
    public function __construct(string $method)
    {
        parent::__construct(
            message: "Costing method not supported: {$method}",
            code: 500,
        );
    }
}
