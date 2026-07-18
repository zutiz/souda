<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\DTOs\ShipmentDTO;
use App\Modules\Order\Enums\FulfillmentStatusEnum;
use App\Modules\Order\Enums\OrderStatusEnum;
use App\Modules\Order\Exceptions\OrderNotFoundException;
use App\Modules\Order\Models\Order;
use Illuminate\Support\Facades\DB;

class FulfillmentService
{
    public function __construct(
        protected OrderService $orderService,
        protected ShipmentService $shipmentService,
    ) {}

    public function fulfillOrder(
        string $orderId,
        string $courier,
        array $items,
        array $recipient,
        int $declaredValue,
        int $codAmount = 0,
        ?int $totalWeightGrams = null,
        ?string $notes = null,
    ): ShipmentDTO {
        $order = Order::find($orderId);

        if (! $order) {
            throw new OrderNotFoundException($orderId);
        }

        $shipment = $this->shipmentService->createShipment(
            orderId: $orderId,
            courier: $courier,
            items: $items,
            recipient: $recipient,
            declaredValue: $declaredValue,
            codAmount: $codAmount,
            totalWeightGrams: $totalWeightGrams,
            notes: $notes,
        );

        $this->recalculateFulfillmentStatus($order);

        if ($order->status === OrderStatusEnum::Processing->value) {
            $this->orderService->updateStatus(
                orderId: $orderId,
                newStatus: OrderStatusEnum::PartiallyShipped->value,
                changedBy: 'system',
            );
        }

        return $shipment;
    }

    public function markShipmentDelivered(string $shipmentId): ShipmentDTO
    {
        $shipment = $this->shipmentService->updateStatus(
            shipmentId: $shipmentId,
            newStatus: 'delivered',
        );

        $order = Order::with('shipments')->find($shipment->orderId);

        if ($order) {
            $this->recalculateFulfillmentStatus($order);

            $allDelivered = $order->shipments->every(
                fn ($s) => ($s->status->value ?? $s->status) === 'delivered'
            );

            if ($allDelivered && $order->status !== OrderStatusEnum::Delivered->value) {
                $this->orderService->updateStatus(
                    orderId: $order->id,
                    newStatus: OrderStatusEnum::Delivered->value,
                    changedBy: 'system',
                    reason: 'All shipments delivered.',
                );
            }
        }

        return $shipment;
    }

    private function recalculateFulfillmentStatus(Order $order): void
    {
        $order->load('shipments');

        $totalItems = (int) $order->items()->sum('quantity');
        $shippedItems = 0;

        foreach ($order->shipments as $shipment) {
            $status = $shipment->status->value ?? $shipment->status;
            if (in_array($status, ['delivered', 'out_for_delivery', 'in_transit', 'picked_up'], true)) {
                $shippedItems += (int) $shipment->items()->sum('quantity');
            }
        }

        $newStatus = FulfillmentStatusEnum::Unfulfilled;

        if ($shippedItems >= $totalItems) {
            $newStatus = FulfillmentStatusEnum::Fulfilled;
        } elseif ($shippedItems > 0) {
            $newStatus = FulfillmentStatusEnum::PartiallyFulfilled;
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update(['fulfillment_status' => $newStatus->value]);
    }
}
