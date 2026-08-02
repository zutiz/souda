<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderAddressDTO;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Enums\OrderStatusEnum;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderStatusChanged;
use App\Modules\Order\Exceptions\InvalidOrderStatusTransition;
use App\Modules\Order\Exceptions\OrderNotFoundException;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderNumberGenerator $numberGenerator,
    ) {}

    public function createOrder(OrderDTO $dto): OrderDTO
    {
        return DB::transaction(function () use ($dto) {
            $orderNumber = $dto->orderNumber ?: $this->numberGenerator->generate();

            $order = Order::create([
                'store_id' => $dto->storeId,
                'order_number' => $orderNumber,
                'customer_id' => $dto->customerId,
                'customer_name' => $dto->customerName,
                'customer_phone' => $dto->customerPhone,
                'customer_email' => $dto->customerEmail,
                'status' => $dto->status,
                'order_type' => $dto->orderType,
                'fulfillment_status' => $dto->fulfillmentStatus,
                'payment_status' => $dto->paymentStatus,
                'currency' => $dto->currency,
                'subtotal' => $dto->subtotal,
                'shipping_total' => $dto->shippingTotal,
                'tax_total' => $dto->taxTotal,
                'discount_total' => $dto->discountTotal,
                'grand_total' => $dto->grandTotal,
                'paid_total' => $dto->paidTotal,
                'refund_total' => $dto->refundTotal,
                'due_total' => $dto->dueTotal,
                'coupon_code' => $dto->couponCode,
                'notes' => $dto->notes,
                'payment_method' => $dto->paymentMethod,
                'payment_reference' => $dto->paymentReference,
                'shipping_name' => $dto->shippingAddress->name,
                'shipping_phone' => $dto->shippingAddress->phone,
                'shipping_address_line_1' => $dto->shippingAddress->addressLine1,
                'shipping_address_line_2' => $dto->shippingAddress->addressLine2,
                'shipping_city' => $dto->shippingAddress->city,
                'shipping_state' => $dto->shippingAddress->state,
                'shipping_postal_code' => $dto->shippingAddress->postalCode,
                'shipping_country' => $dto->shippingAddress->country,
                'source' => $dto->source,
                'created_by' => $dto->createdBy,
                'placed_at' => $dto->placedAt,
                'metadata' => $dto->metadata,
            ]);

            foreach ($dto->lineItems as $item) {
                $order->items()->create([
                    'product_id' => $item->productId,
                    'variant_id' => $item->variantId,
                    'warehouse_id' => $item->warehouseId,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'total_price' => $item->totalPrice,
                    'tax_amount' => $item->taxAmount ?? 0,
                    'discount_amount' => $item->discountAmount ?? 0,
                    'metadata' => $item->metadata,
                ]);
            }

            $order->statusHistories()->create([
                'to_status' => $dto->status,
                'changed_by' => $dto->createdBy,
            ]);

            $result = $this->toDTO($order);

            (new OrderCreated(
                order: $result,
                correlationId: (string) Str::ulid(),
            ))->dispatch();

            return $result;
        });
    }

    public function getOrder(string $orderId): OrderDTO
    {
        $order = Order::with(['items', 'statusHistories'])->find($orderId);

        if (! $order) {
            throw new OrderNotFoundException($orderId);
        }

        return $this->toDTO($order);
    }

    public function updateStatus(string $orderId, string $newStatus, ?string $reason = null, ?string $changedBy = null): OrderDTO
    {
        $order = Order::with(['items'])->find($orderId);

        if (! $order) {
            throw new OrderNotFoundException($orderId);
        }

        $currentEnum = OrderStatusEnum::tryFrom($order->status);
        $targetEnum = OrderStatusEnum::tryFrom($newStatus);

        if ($currentEnum && $targetEnum && ! $currentEnum->canTransitionTo($targetEnum)) {
            throw new InvalidOrderStatusTransition($order->status, $newStatus);
        }

        return DB::transaction(function () use ($order, $newStatus, $reason, $changedBy) {
            $fromStatus = $order->status;

            $order->update(['status' => $newStatus]);

            $history = $order->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'changed_by' => $changedBy,
                'reason' => $reason,
            ]);

            $result = $this->toDTO($order);

            (new OrderStatusChanged(
                order: $result,
                previousStatus: $fromStatus,
                newStatus: $newStatus,
                reason: $reason,
                changedBy: $changedBy,
            ))->dispatch();

            return $result;
        });
    }

    public function cancelOrder(string $orderId, string $reason, ?string $cancelledBy = null): OrderDTO
    {
        $dto = $this->updateStatus($orderId, OrderStatusEnum::Cancelled->value, $reason, $cancelledBy);

        $order = Order::find($orderId);
        $order->update(['cancelled_at' => now()]);

        (new OrderCancelled(
            order: $dto,
            reason: $reason,
        ))->dispatch();

        return $dto;
    }

    public function listOrders(?string $storeId, array $filters = []): array
    {
        $query = Order::with(['items']);

        if (! empty($storeId)) {
            $query->where('store_id', $storeId);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['order_type'])) {
            $query->where('order_type', $filters['order_type']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('placed_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('placed_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                    );
            });
        }

        $query->orderBy('created_at', 'desc');

        return $query->get()->map(fn (Order $order) => $this->toDTO($order))->toArray();
    }

    private function toDTO(Order $order): OrderDTO
    {
        $shippingAddress = new OrderAddressDTO(
            name: $order->shipping_name ?? $order->customer_name ?? '',
            phone: $order->shipping_phone ?? $order->customer_phone ?? '',
            addressLine1: $order->shipping_address_line_1 ?? '',
            addressLine2: $order->shipping_address_line_2 ?? null,
            city: $order->shipping_city ?? '',
            state: $order->shipping_state ?? null,
            postalCode: $order->shipping_postal_code ?? '',
            country: $order->shipping_country ?? '',
            email: $order->customer_email ?? null,
        );

        $billingAddress = null;
        if ($order->billing_name || $order->billing_address_line_1) {
            $billingAddress = new OrderAddressDTO(
                name: $order->billing_name ?? '',
                phone: $order->billing_phone ?? '',
                addressLine1: $order->billing_address_line_1 ?? '',
                addressLine2: $order->billing_address_line_2 ?? null,
                city: $order->billing_city ?? '',
                state: $order->billing_state ?? null,
                postalCode: $order->billing_postal_code ?? '',
                country: $order->billing_country ?? '',
                email: $order->customer_email ?? null,
            );
        }

        $lineItems = $order->relationLoaded('items')
            ? $order->items->map(fn (OrderItem $item) => new LineItemDTO(
                productId: $item->product_id ?? '',
                variantId: $item->variant_id,
                name: $item->name,
                sku: $item->sku ?? '',
                quantity: $item->quantity,
                unitPrice: $item->unit_price,
                totalPrice: $item->total_price,
                taxAmount: $item->tax_amount,
                discountAmount: $item->discount_amount,
                warehouseId: $item->warehouse_id,
                metadata: $item->metadata,
                barcode: $item->barcode,
                id: $item->id,
            ))->toArray()
            : [];

        return new OrderDTO(
            orderId: $order->id,
            orderNumber: $order->order_number,
            tenantId: $order->tenant_id ?? '',
            customerId: $order->customer_id,
            status: $order->status->value ?? $order->status,
            subtotal: $order->subtotal,
            taxTotal: $order->tax_total,
            discountTotal: $order->discount_total,
            grandTotal: $order->grand_total,
            currency: $order->currency,
            shippingAddress: $shippingAddress,
            billingAddress: $billingAddress,
            lineItems: $lineItems,
            couponCode: $order->coupon_code,
            notes: $order->notes,
            paymentMethod: $order->payment_method,
            placedAt: $order->placed_at ? CarbonImmutable::createFromTimestamp($order->placed_at->timestamp) : new CarbonImmutable,
            metadata: $order->metadata,
            storeId: $order->store_id,
            orderType: $order->order_type->value ?? $order->order_type,
            fulfillmentStatus: $order->fulfillment_status->value ?? $order->fulfillment_status,
            paymentStatus: $order->payment_status,
            shippingTotal: $order->shipping_total,
            paidTotal: $order->paid_total,
            refundTotal: $order->refund_total,
            dueTotal: $order->due_total,
            customerName: $order->customer_name,
            customerPhone: $order->customer_phone,
            customerEmail: $order->customer_email,
            paymentReference: $order->payment_reference,
            source: $order->source,
            createdBy: $order->created_by,
        );
    }
}
