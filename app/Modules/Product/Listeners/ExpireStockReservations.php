<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Jobs\ExpireStockReservationsJob;

class ExpireStockReservations
{
    public function handle(): void
    {
        ExpireStockReservationsJob::dispatch();
    }
}
