<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Contracts\Courier\CourierShipmentData;
use App\Modules\Order\DTOs\ShipmentDTO;
use App\Modules\Order\DTOs\ShipmentItemDTO;
use App\Modules\Order\Enums\ShipmentStatusEnum;
use App\Modules\Order\Events\ShipmentCreated;
use App\Modules\Order\Events\ShipmentDelivered;
use App\Modules\Order\Events\ShipmentDeliveryFailed;
use App\Modules\Order\Events\ShipmentInTransit;
use App\Modules\Order\Events\ShipmentOutForDelivery;
use App\Modules\Order\Events\ShipmentPickedUp;
use App\Modules\Order\Events\ShipmentReturnedToSender;
use App\Modules\Order\Events\ShipmentStatusChanged;
use App\Modules\Order\Exceptions\InvalidShipmentStatusTransition;
use App\Modules\Order\Exceptions\ShipmentCreationFailedException;
use App\Modules\Order\Exceptions\ShipmentNotFoundException;
use App\Modules\Order\Models\Shipment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function __construct(
        protected OrderNumberGenerator $numberGenerator,
        protected CourierManager $courierManager,
    ) {}

    public function createShipment(
        string $orderId,
        string $courier,
        array $items,
        array $recipient,
        int $declaredValue,
        int $codAmount = 0,
        ?int $totalWeightGrams = null,
        ?string $notes = null,
        ?string $serviceType = 'standard',
    ): ShipmentDTO {
        $shipmentNumber = $this->numberGenerator->generateShipmentNumber();

        return DB::transaction(function () use (
            $orderId, $courier, $items, $recipient, $declaredValue,
            $codAmount, $totalWeightGrams, $notes, $shipmentNumber, $serviceType,
        ) {
            $shipment = Shipment::create([
                'order_id' => $orderId,
                'shipment_number' => $shipmentNumber,
                'courier' => $courier,
                'status' => ShipmentStatusEnum::Pending->value,
                'recipient_name' => $recipient['name'] ?? null,
                'recipient_phone' => $recipient['phone'] ?? null,
                'recipient_address' => $recipient['address'] ?? null,
                'recipient_city' => $recipient['city'] ?? null,
                'recipient_postal_code' => $recipient['postal_code'] ?? null,
                'declared_value' => $declaredValue,
                'cod_amount' => $codAmount,
                'total_weight_grams' => $totalWeightGrams,
                'total_items' => count($items),
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $shipment->items()->create([
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);
            }

            try {
                $courierData = new CourierShipmentData(
                    orderReference: $shipmentNumber,
                    recipientName: $recipient['name'] ?? '',
                    recipientPhone: $recipient['phone'] ?? '',
                    recipientAddress: $recipient['address'] ?? '',
                    recipientCity: $recipient['city'] ?? '',
                    recipientPostalCode: $recipient['postal_code'] ?? null,
                    totalWeightGrams: $totalWeightGrams ?? 0,
                    declaredValue: $declaredValue,
                    codAmount: $codAmount,
                    serviceType: $serviceType,
                    notes: $notes,
                    items: $items,
                );

                $courierResult = $this->courierManager->createShipment($courier, $courierData);

                $updateData = ['courier_response' => $courierResult->rawResponse];

                if ($courierResult->trackingNumber) {
                    $updateData['tracking_number'] = $courierResult->trackingNumber;
                }

                if ($courierResult->trackingUrl) {
                    $updateData['tracking_url'] = $courierResult->trackingUrl;
                }

                if ($courierResult->labelUrl) {
                    $updateData['label_url'] = $courierResult->labelUrl;
                }

                if ($courierResult->shippingCost !== null) {
                    $updateData['shipping_cost'] = $courierResult->shippingCost;
                }

                if ($courierResult->estimatedDeliveryDate) {
                    $updateData['estimated_delivery'] = $courierResult->estimatedDeliveryDate;
                }

                if ($courierResult->trackingNumber) {
                    $updateData['status'] = ShipmentStatusEnum::LabelCreated->value;
                }

                $shipment->update($updateData);
            } catch (\Throwable $e) {
                $shipment->update([
                    'courier_response' => ['error' => $e->getMessage()],
                ]);

                throw new ShipmentCreationFailedException(
                    "Shipment created but courier API call failed: {$e->getMessage()}",
                    $e,
                );
            }

            $result = $this->toDTO($shipment);

            (new ShipmentCreated(
                shipment: $result,
                orderId: $orderId,
            ))->dispatch();

            return $result;
        });
    }

    public function updateStatus(string $shipmentId, string $newStatus, ?string $notes = null): ShipmentDTO
    {
        $shipment = Shipment::with(['items'])->find($shipmentId);

        if (! $shipment) {
            throw new ShipmentNotFoundException($shipmentId);
        }

        $currentEnum = ShipmentStatusEnum::tryFrom($shipment->status);
        $targetEnum = ShipmentStatusEnum::tryFrom($newStatus);

        if ($currentEnum && $targetEnum && ! $currentEnum->canTransitionTo($targetEnum)) {
            throw new InvalidShipmentStatusTransition($shipment->status, $newStatus);
        }

        return DB::transaction(function () use ($shipment, $newStatus, $notes) {
            $fromStatus = $shipment->status;

            $shipment->update(['status' => $newStatus, 'notes' => $notes ?? $shipment->notes]);

            if ($newStatus === ShipmentStatusEnum::Delivered->value) {
                $shipment->update(['delivered_at' => now()]);
            }

            if ($newStatus === ShipmentStatusEnum::PickedUp->value) {
                $shipment->update(['shipped_at' => now()]);
            }

            $result = $this->toDTO($shipment->fresh(['items']));

            (new ShipmentStatusChanged(
                shipment: $result,
                previousStatus: $fromStatus,
                newStatus: $newStatus,
            ))->dispatch();

            $this->dispatchGranularEvent($result, $newStatus, $notes);

            if ($newStatus === ShipmentStatusEnum::Delivered->value) {
                (new ShipmentDelivered(
                    shipment: $result,
                ))->dispatch();
            }

            return $result;
        });
    }

    public function markDeliveryFailed(string $shipmentId, string $reason): ShipmentDTO
    {
        return $this->updateStatus($shipmentId, ShipmentStatusEnum::DeliveryFailed->value, $reason);
    }

    public function markReturnedToSender(string $shipmentId, ?string $reason = null): ShipmentDTO
    {
        return $this->updateStatus($shipmentId, ShipmentStatusEnum::ReturnedToSender->value, $reason);
    }

    private function dispatchGranularEvent(ShipmentDTO $shipment, string $status, ?string $notes): void
    {
        match ($status) {
            ShipmentStatusEnum::PickedUp->value => (new ShipmentPickedUp(shipment: $shipment))->dispatch(),
            ShipmentStatusEnum::InTransit->value => (new ShipmentInTransit(shipment: $shipment))->dispatch(),
            ShipmentStatusEnum::OutForDelivery->value => (new ShipmentOutForDelivery(shipment: $shipment))->dispatch(),
            ShipmentStatusEnum::DeliveryFailed->value => (new ShipmentDeliveryFailed(shipment: $shipment, reason: $notes ?? 'Delivery failed'))->dispatch(),
            ShipmentStatusEnum::ReturnedToSender->value => (new ShipmentReturnedToSender(shipment: $shipment, reason: $notes))->dispatch(),
            default => null,
        };
    }

    public function trackShipment(string $shipmentId): array
    {
        $shipment = Shipment::find($shipmentId);

        if (! $shipment) {
            throw new ShipmentNotFoundException($shipmentId);
        }

        if (! $shipment->tracking_number || ! $shipment->courier) {
            return $this->toDTO($shipment)->toArray();
        }

        try {
            $tracking = $this->courierManager->trackShipment(
                $shipment->courier,
                $shipment->tracking_number,
            );

            return array_merge(
                $this->toDTO($shipment)->toArray(),
                ['tracking' => $tracking->toArray()],
            );
        } catch (\Throwable) {
            return $this->toDTO($shipment)->toArray();
        }
    }

    public function getShipment(string $shipmentId): ShipmentDTO
    {
        $shipment = Shipment::with(['items'])->find($shipmentId);

        if (! $shipment) {
            throw new ShipmentNotFoundException($shipmentId);
        }

        return $this->toDTO($shipment);
    }

    public function listShipments(?string $orderId = null): array
    {
        $query = Shipment::with(['items']);

        if (! empty($orderId)) {
            $query->where('order_id', $orderId);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Shipment $shipment) => $this->toDTO($shipment))
            ->toArray();
    }

    private function toDTO(Shipment $shipment): ShipmentDTO
    {
        $items = $shipment->relationLoaded('items')
            ? $shipment->items->map(fn ($item) => new ShipmentItemDTO(
                name: $item->name,
                quantity: $item->quantity,
                unitPrice: $item->unit_price,
                productId: $item->product_id,
                variantId: $item->variant_id,
                sku: $item->sku,
                orderItemId: $item->order_item_id,
                id: $item->id,
            ))->toArray()
            : [];

        return new ShipmentDTO(
            shipmentId: $shipment->id,
            orderId: $shipment->order_id,
            shipmentNumber: $shipment->shipment_number,
            status: $shipment->status->value ?? $shipment->status,
            courier: $shipment->courier,
            courierService: $shipment->courier_service,
            trackingNumber: $shipment->tracking_number,
            trackingUrl: $shipment->tracking_url,
            labelUrl: $shipment->label_url,
            recipientName: $shipment->recipient_name,
            recipientPhone: $shipment->recipient_phone,
            recipientAddress: $shipment->recipient_address,
            recipientCity: $shipment->recipient_city,
            recipientPostalCode: $shipment->recipient_postal_code,
            shippingCost: $shipment->shipping_cost,
            codAmount: $shipment->cod_amount,
            declaredValue: $shipment->declared_value,
            totalWeightGrams: $shipment->total_weight_grams,
            totalItems: $shipment->total_items,
            notes: $shipment->notes,
            courierResponse: $shipment->courier_response,
            items: $items,
            shippedAt: $shipment->shipped_at ? CarbonImmutable::createFromTimestamp($shipment->shipped_at->timestamp) : null,
            estimatedDelivery: $shipment->estimated_delivery ? CarbonImmutable::createFromTimestamp($shipment->estimated_delivery->timestamp) : null,
            deliveredAt: $shipment->delivered_at ? CarbonImmutable::createFromTimestamp($shipment->delivered_at->timestamp) : null,
            createdAt: CarbonImmutable::createFromTimestamp($shipment->created_at->timestamp),
        );
    }
}
