<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use Carbon\CarbonImmutable;

readonly class OrderDTO
{
    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public string $tenantId,
        public ?string $customerId,
        public string $status,
        public int $subtotal,
        public int $taxTotal,
        public int $discountTotal,
        public int $grandTotal,
        public string $currency,
        public OrderAddressDTO $shippingAddress,
        public ?OrderAddressDTO $billingAddress,
        public array $lineItems,
        public ?string $couponCode,
        public ?string $notes,
        public ?string $paymentMethod,
        public CarbonImmutable $placedAt,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            orderNumber: $data['order_number'],
            tenantId: $data['tenant_id'],
            customerId: $data['customer_id'] ?? null,
            status: $data['status'],
            subtotal: (int) $data['subtotal'],
            taxTotal: (int) ($data['tax_total'] ?? 0),
            discountTotal: (int) ($data['discount_total'] ?? 0),
            grandTotal: (int) $data['grand_total'],
            currency: $data['currency'],
            shippingAddress: OrderAddressDTO::fromArray($data['shipping_address']),
            billingAddress: isset($data['billing_address']) ? OrderAddressDTO::fromArray($data['billing_address']) : null,
            lineItems: array_map(fn (array $item) => LineItemDTO::fromArray($item), $data['line_items'] ?? []),
            couponCode: $data['coupon_code'] ?? null,
            notes: $data['notes'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            placedAt: new CarbonImmutable($data['placed_at']),
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'tenant_id' => $this->tenantId,
            'customer_id' => $this->customerId,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->taxTotal,
            'discount_total' => $this->discountTotal,
            'grand_total' => $this->grandTotal,
            'currency' => $this->currency,
            'shipping_address' => $this->shippingAddress->toArray(),
            'billing_address' => $this->billingAddress?->toArray(),
            'line_items' => array_map(fn (LineItemDTO $item) => $item->toArray(), $this->lineItems),
            'coupon_code' => $this->couponCode,
            'notes' => $this->notes,
            'payment_method' => $this->paymentMethod,
            'placed_at' => $this->placedAt->toISOString(),
            'metadata' => $this->metadata,
        ];
    }
}
