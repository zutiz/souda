<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Services\StockReservationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireStockReservationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct()
    {
        $this->onQueue('low');
    }

    public function handle(StockReservationService $reservationService): void
    {
        $expired = $reservationService->expireOldReservations();

        logger()->info('Expired stock reservations', ['count' => $expired]);
    }
}
