<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\ShipmentCreated;
use App\Modules\Order\Models\Order;
use App\Notifications\ShipmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyCustomerOnShipment implements ShouldQueue
{
    public string $queue = 'notifications';

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function handle(ShipmentCreated $event): void
    {
        $shipment = $event->shipment;

        if (! $shipment->trackingNumber) {
            return;
        }

        $order = Order::with('tenant')->find($shipment->orderId);

        if (! $order || ! $order->customer_email) {
            Log::info('Shipment notification skipped — no customer email', [
                'shipment_number' => $shipment->shipmentNumber,
                'order_id' => $shipment->orderId,
            ]);

            return;
        }

        Notification::route('mail', $order->customer_email)
            ->notify(new ShipmentNotification($shipment));

        Log::info('Shipment notification sent', [
            'shipment_number' => $shipment->shipmentNumber,
            'tracking_number' => $shipment->trackingNumber,
            'courier' => $shipment->courier,
            'customer_email' => $order->customer_email,
        ]);
    }

    public function failed(ShipmentCreated $event, \Throwable $e): void
    {
        Log::error('NotifyCustomerOnShipment failed', [
            'shipment_id' => $event->shipment->shipmentId,
            'error' => $e->getMessage(),
        ]);
    }
}
