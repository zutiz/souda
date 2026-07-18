<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class OrderNotFoundException extends \RuntimeException
{
    public function __construct(string $orderId)
    {
        parent::__construct("Order '{$orderId}' not found.");
    }
}
