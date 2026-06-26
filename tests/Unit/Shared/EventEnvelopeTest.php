<?php

declare(strict_types=1);

use App\Modules\Shared\DTOs\EventEnvelope;

test('event envelope can be created', function () {
    $envelope = EventEnvelope::make(
        eventName: 'test.event',
        payload: ['key' => 'value'],
        tenantId: 'tenant-1',
    );

    expect($envelope->eventName)->toBe('test.event')
        ->and($envelope->payload)->toBe(['key' => 'value'])
        ->and($envelope->tenantId)->toBe('tenant-1')
        ->and($envelope->eventId)->not->toBeNull()
        ->and($envelope->correlationId)->not->toBeNull()
        ->and($envelope->causationId)->toBeNull();
});

test('event envelope can be serialized to array and back', function () {
    $original = EventEnvelope::make(
        eventName: 'test.event',
        payload: ['order_id' => '123'],
        correlationId: 'corr-1',
        causationId: 'cause-1',
        tenantId: 'tenant-1',
    );

    $array = $original->toArray();
    $restored = EventEnvelope::fromArray($array);

    expect($restored->eventId)->toBe($original->eventId)
        ->and($restored->eventName)->toBe('test.event')
        ->and($restored->correlationId)->toBe('corr-1')
        ->and($restored->causationId)->toBe('cause-1')
        ->and($restored->tenantId)->toBe('tenant-1')
        ->and($restored->payload)->toBe(['order_id' => '123']);
});

test('event envelope idempotency key consists of listener and event id', function () {
    $envelope = EventEnvelope::make(eventName: 'test.event', payload: []);

    $key = $envelope->idempotencyKey('TestListener');

    expect($key)->toContain('TestListener')
        ->and($key)->toContain($envelope->eventId);
});
