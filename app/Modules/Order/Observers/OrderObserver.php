<?php

declare(strict_types=1);

namespace App\Modules\Order\Observers;

use App\Modules\Order\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if ($order->placed_at === null) {
            $order->placed_at = now();
        }
    }
}
