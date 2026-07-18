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
        public string $status,
        public int $subtotal,
        public int $grandTotal,
        public string $currency,
        public OrderAddressDTO $shippingAddress,
        public array $lineItems,
        public CarbonImmutable $placedAt,
        public ?string $customerId = null,
        public ?OrderAddressDTO $billingAddress = null,
        public ?string $couponCode = null,
        public ?string $notes = null,
        public ?string $paymentMethod = null,
        public ?array $metadata = null,
        public string $storeId = '',
        public string $orderType = 'in_store',
        public string $fulfillmentStatus = 'unfulfilled',
        public string $paymentStatus = 'pending',
        public int $taxTotal = 0,
        public int $discountTotal = 0,
        public int $shippingTotal = 0,
        public int $paidTotal = 0,
        public int $refundTotal = 0,
        public int $dueTotal = 0,
        public ?string $customerName = null,
        public ?string $customerPhone = null,
        public ?string $customerEmail = null,
        public ?string $paymentReference = null,
        public ?string $source = 'pos',
        public ?string $createdBy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            orderNumber: $data['order_number'],
            tenantId: $data['tenant_id'],
            status: $data['status'],
            subtotal: (int) $data['subtotal'],
            grandTotal: (int) $data['grand_total'],
            currency: $data['currency'],
            shippingAddress: OrderAddressDTO::fromArray($data['shipping_address']),
            lineItems: array_map(fn (array $item) => LineItemDTO::fromArray($item), $data['line_items'] ?? []),
            placedAt: new CarbonImmutable($data['placed_at']),
            customerId: $data['customer_id'] ?? null,
            billingAddress: isset($data['billing_address']) ? OrderAddressDTO::fromArray($data['billing_address']) : null,
            couponCode: $data['coupon_code'] ?? null,
            notes: $data['notes'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            metadata: $data['metadata'] ?? null,
            storeId: $data['store_id'] ?? '',
            orderType: $data['order_type'] ?? 'in_store',
            fulfillmentStatus: $data['fulfillment_status'] ?? 'unfulfilled',
            paymentStatus: $data['payment_status'] ?? 'pending',
            taxTotal: (int) ($data['tax_total'] ?? 0),
            discountTotal: (int) ($data['discount_total'] ?? 0),
            shippingTotal: (int) ($data['shipping_total'] ?? 0),
            paidTotal: (int) ($data['paid_total'] ?? 0),
            refundTotal: (int) ($data['refund_total'] ?? 0),
            dueTotal: (int) ($data['due_total'] ?? 0),
            customerName: $data['customer_name'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            paymentReference: $data['payment_reference'] ?? null,
            source: $data['source'] ?? 'pos',
            createdBy: $data['created_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'tenant_id' => $this->tenantId,
            'store_id' => $this->storeId,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'status' => $this->status,
            'order_type' => $this->orderType,
            'fulfillment_status' => $this->fulfillmentStatus,
            'payment_status' => $this->paymentStatus,
            'subtotal' => $this->subtotal,
            'shipping_total' => $this->shippingTotal,
            'tax_total' => $this->taxTotal,
            'discount_total' => $this->discountTotal,
            'grand_total' => $this->grandTotal,
            'paid_total' => $this->paidTotal,
            'refund_total' => $this->refundTotal,
            'due_total' => $this->dueTotal,
            'currency' => $this->currency,
            'shipping_address' => $this->shippingAddress->toArray(),
            'billing_address' => $this->billingAddress?->toArray(),
            'line_items' => array_map(fn (LineItemDTO $item) => $item->toArray(), $this->lineItems),
            'coupon_code' => $this->couponCode,
            'notes' => $this->notes,
            'payment_method' => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'source' => $this->source,
            'created_by' => $this->createdBy,
            'placed_at' => $this->placedAt->toISOString(),
            'metadata' => $this->metadata,
        ];
    }
}
