<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class OrderBackorderedException extends DomainException
{
    public function __construct(int $orderId)
    {
        parent::__construct(
            message: "Order {$orderId} has been backordered due to insufficient stock",
            code: 422,
        );
    }
}
