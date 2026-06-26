<?php

namespace App\Modules\Billing\Events;

use App\Modules\Billing\DTOs\OverageInvoiceDTO;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatOverageInvoiced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OverageInvoiceDTO $invoice,
    ) {}
}
