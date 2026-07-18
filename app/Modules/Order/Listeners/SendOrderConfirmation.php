<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use App\Notifications\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderConfirmation implements ShouldQueue
{
    public string $queue = 'notifications';

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if ($order->customerEmail) {
            Notification::route('mail', $order->customerEmail)
                ->notify(new OrderConfirmation($order));
        }

        if ($order->customerPhone) {
            Log::info('SMS order confirmation queued', [
                'order_number' => $order->orderNumber,
                'phone' => $order->customerPhone,
            ]);
        }
    }

    public function failed(OrderCreated $event, \Throwable $e): void
    {
        Log::error('SendOrderConfirmation failed', [
            'order_id' => $event->order->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
