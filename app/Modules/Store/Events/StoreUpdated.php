<?php

declare(strict_types=1);

namespace App\Modules\Store\Events;

use App\Modules\Store\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Store $store,
    ) {}
}
