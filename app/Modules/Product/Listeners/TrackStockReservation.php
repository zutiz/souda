<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\StockReservationCreated;

class TrackStockReservation
{
    public function handle(StockReservationCreated $event): void
    {
        logger()->info('Stock reservation created', [
            'reservation_id' => $event->reservationId,
            'product_id' => $event->productId,
            'variant_id' => $event->variantId,
            'quantity' => $event->quantity,
            'reference_type' => $event->referenceType,
            'reference_id' => $event->referenceId,
        ]);
    }
}
