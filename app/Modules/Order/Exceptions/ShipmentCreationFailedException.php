<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class ShipmentCreationFailedException extends \RuntimeException
{
    public function __construct(string $message = 'Failed to create shipment.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
