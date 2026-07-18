<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class ShipmentNotFoundException extends \RuntimeException
{
    public function __construct(string $shipmentId)
    {
        parent::__construct("Shipment '{$shipmentId}' not found.");
    }
}
