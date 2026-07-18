<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusHistory;
use Carbon\CarbonImmutable;

class OrderTimelineService
{
    public function getTimeline(string $orderId): array
    {
        $history = OrderStatusHistory::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (OrderStatusHistory $entry) => [
                'id' => $entry->id,
                'from_status' => $entry->from_status,
                'to_status' => $entry->to_status,
                'changed_by' => $entry->changed_by,
                'reason' => $entry->reason,
                'occurred_at' => CarbonImmutable::createFromTimestamp($entry->created_at->timestamp)->toISOString(),
                'type' => 'status_change',
            ])
            ->toArray();

        $order = Order::find($orderId);

        if ($order) {
            $events = $history;

            if ($order->placed_at) {
                array_unshift($events, [
                    'id' => 'placed',
                    'from_status' => null,
                    'to_status' => $order->status,
                    'changed_by' => $order->created_by,
                    'reason' => 'Order placed',
                    'occurred_at' => CarbonImmutable::createFromTimestamp($order->placed_at->timestamp)->toISOString(),
                    'type' => 'placed',
                ]);
            }
        }

        return $events ?? $history;
    }
}
