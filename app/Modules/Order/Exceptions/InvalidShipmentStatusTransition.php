<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class InvalidShipmentStatusTransition extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot transition shipment status from '{$from}' to '{$to}'.");
    }
}
