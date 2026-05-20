<?php

declare(strict_types=1);

namespace App\Modules\CRM\DTOs;

use Carbon\CarbonImmutable;

readonly class CustomerDTO
{
    public function __construct(
        public string $customerId,
        public string $tenantId,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $company,
        public ?string $taxNumber,
        public string $type,
        public string $status,
        public ?array $billingAddress,
        public ?array $shippingAddress,
        public ?array $metadata,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerId: $data['customer_id'],
            tenantId: $data['tenant_id'],
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            company: $data['company'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            type: $data['type'],
            status: $data['status'],
            billingAddress: $data['billing_address'] ?? null,
            shippingAddress: $data['shipping_address'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: new CarbonImmutable($data['created_at']),
            updatedAt: new CarbonImmutable($data['updated_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'tax_number' => $this->taxNumber,
            'type' => $this->type,
            'status' => $this->status,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt->toISOString(),
            'updated_at' => $this->updatedAt->toISOString(),
        ];
    }
}
