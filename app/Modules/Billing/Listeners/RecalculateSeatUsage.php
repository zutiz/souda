<?php

namespace App\Modules\Billing\Listeners;

use App\Modules\Billing\Events\SeatAllocated;
use App\Modules\Billing\Events\SeatReleased;
use App\Modules\Billing\Services\OverageInvoiceService;
use App\Modules\Billing\Services\SeatService;
use Illuminate\Support\Facades\Log;

class RecalculateSeatUsage
{
    public function __construct(
        private readonly SeatService $seatService,
        private readonly OverageInvoiceService $overageInvoiceService,
    ) {}

    public function handleSeatAllocated(SeatAllocated $event): void
    {
        if ($event->isOverage) {
            $this->overageInvoiceService->generateOverageInvoice(
                $event->allocation->tenant_id
            );
        }

        Log::info('Seat usage recalculated after allocation', [
            'tenant_id' => $event->allocation->tenant_id,
            'consumed' => $this->seatService->getConsumedSeatCount($event->allocation->tenant_id),
        ]);
    }

    public function handleSeatReleased(SeatReleased $event): void
    {
        Log::info('Seat usage recalculated after release', [
            'tenant_id' => $event->allocation->tenant_id,
            'consumed' => $this->seatService->getConsumedSeatCount($event->allocation->tenant_id),
        ]);
    }
}
