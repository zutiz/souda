<?php

declare(strict_types=1);

namespace App\Modules\Product\Observers;

use App\Modules\Product\Enums\StockReservationStatusEnum;
use App\Modules\Product\Events\StockReservationCreated;
use App\Modules\Product\Events\StockReservationExpired;
use App\Modules\Product\Models\StockReservation;

class StockReservationObserver
{
    public function created(StockReservation $reservation): void
    {
        event(new StockReservationCreated(
            reservationId: $reservation->id,
            productId: $reservation->product_id ?? $reservation->variant_id ?? 'unknown',
            variantId: $reservation->variant_id,
            quantity: $reservation->quantity,
            referenceType: $reservation->reference_type,
            referenceId: $reservation->reference_id,
        ));
    }

    public function updated(StockReservation $reservation): void
    {
        if ($reservation->wasChanged('status') && $reservation->status === StockReservationStatusEnum::Expired) {
            event(new StockReservationExpired(
                reservationId: $reservation->id,
                productId: $reservation->product_id ?? $reservation->variant_id ?? 'unknown',
                variantId: $reservation->variant_id,
            ));
        }
    }
}
