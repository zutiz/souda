<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Enums\OrderStatusEnum;
use App\Modules\Order\Events\ShipmentDelivered;
use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteOrder implements ShouldQueue
{
    public string $queue = 'default';

    public int $tries = 3;

    public function handle(ShipmentDelivered $event): void
    {
        $order = Order::with('shipments')->find($event->shipment->orderId);

        if (! $order) {
            return;
        }

        $allDelivered = $order->shipments->every(
            fn ($s) => ($s->status->value ?? $s->status) === 'delivered'
        );

        if (! $allDelivered) {
            return;
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update([
                'status' => OrderStatusEnum::Completed->value,
                'completed_at' => now(),
            ]);

        Log::info('Order auto-completed after all shipments delivered', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function failed(ShipmentDelivered $event, \Throwable $e): void
    {
        Log::error('AutoCompleteOrder failed', [
            'order_id' => $event->shipment->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
