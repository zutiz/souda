<?php

declare(strict_types=1);

namespace App\Modules\Order\Observers;

use App\Modules\Order\Models\Shipment;

class ShipmentObserver
{
    public function creating(Shipment $shipment): void
    {
        if ($shipment->total_items === 0) {
            $shipment->total_items = $shipment->items()->count();
        }
    }
}
