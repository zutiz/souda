<?php

namespace App\Modules\Billing\Events;

use App\Modules\Billing\Models\SeatAllocation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatReleased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SeatAllocation $allocation,
    ) {}
}
