<?php

declare(strict_types=1);

use App\Modules\CRM\DTOs\CustomerDTO;

test('customer dto can be created from array', function () {
    $dto = CustomerDTO::fromArray([
        'customer_id' => 'cust-1',
        'tenant_id' => 'tenant-1',
        'name' => 'Acme Corp',
        'email' => 'billing@acme.com',
        'phone' => '+8801712345678',
        'company' => 'Acme Corporation',
        'tax_number' => 'TAX-12345',
        'type' => 'business',
        'status' => 'active',
        'created_at' => '2026-01-15T00:00:00Z',
        'updated_at' => '2026-05-01T00:00:00Z',
    ]);

    expect($dto->customerId)->toBe('cust-1')
        ->and($dto->name)->toBe('Acme Corp')
        ->and($dto->email)->toBe('billing@acme.com')
        ->and($dto->type)->toBe('business')
        ->and($dto->status)->toBe('active');
});

test('customer dto serializes to array', function () {
    $dto = CustomerDTO::fromArray([
        'customer_id' => 'cust-2',
        'tenant_id' => 'tenant-1',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'type' => 'individual',
        'status' => 'active',
        'created_at' => '2026-03-10T00:00:00Z',
        'updated_at' => '2026-05-01T00:00:00Z',
    ]);

    $array = $dto->toArray();

    expect($array['customer_id'])->toBe('cust-2')
        ->and($array['name'])->toBe('John Doe')
        ->and($array['phone'])->toBeNull();
});

test('customer dto handles optional billing and shipping addresses', function () {
    $dto = CustomerDTO::fromArray([
        'customer_id' => 'cust-3',
        'tenant_id' => 'tenant-1',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'type' => 'individual',
        'status' => 'active',
        'billing_address' => ['city' => 'Dhaka'],
        'shipping_address' => ['city' => 'Chittagong'],
        'created_at' => '2026-01-01T00:00:00Z',
        'updated_at' => '2026-01-01T00:00:00Z',
    ]);

    expect($dto->billingAddress)->toBe(['city' => 'Dhaka'])
        ->and($dto->shippingAddress)->toBe(['city' => 'Chittagong']);
});
