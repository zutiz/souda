<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\StockReservationExpired;

class ReleaseExpiredStock
{
    public function handle(StockReservationExpired $event): void
    {
        logger()->info('Stock reservation expired and released', [
            'reservation_id' => $event->reservationId,
            'product_id' => $event->productId,
            'variant_id' => $event->variantId,
        ]);
    }
}
