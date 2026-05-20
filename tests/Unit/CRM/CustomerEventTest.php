<?php

use App\Modules\CRM\DTOs\CustomerDTO;
use App\Modules\CRM\Events\CustomerCreated;
use App\Modules\CRM\Events\CustomerUpdated;
use App\Modules\Shared\Contracts\DomainEvent;

function customerEventTestCustomerDTO(): CustomerDTO
{
    return CustomerDTO::fromArray([
        'customer_id' => 'cust-1',
        'tenant_id' => 'tenant-1',
        'name' => 'Acme Corp',
        'email' => 'billing@acme.com',
        'type' => 'business',
        'status' => 'active',
        'created_at' => '2026-01-15T00:00:00Z',
        'updated_at' => '2026-05-01T00:00:00Z',
    ]);
}

test('customer created implements domain event', function () {
    $event = new CustomerCreated(customerEventTestCustomerDTO());

    expect($event)->toBeInstanceOf(DomainEvent::class)
        ->and($event->getEventName())->toBe('crm.customer.created');
});

test('customer created envelope has customer payload', function () {
    $event = new CustomerCreated(customerEventTestCustomerDTO());
    $envelope = $event->toEnvelope();

    expect($envelope->payload['customer']['name'])->toBe('Acme Corp');
});

test('customer updated event contains changed fields', function () {
    $event = new CustomerUpdated(
        customer: customerEventTestCustomerDTO(),
        changedFields: ['name', 'email'],
    );

    expect($event->getEventName())->toBe('crm.customer.updated')
        ->and($event->changedFields)->toBe(['name', 'email']);
});
