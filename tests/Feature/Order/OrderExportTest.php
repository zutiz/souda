<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Store\Models\Store;
use App\Tenancy\TenantManager;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    app(TenantManager::class)->initialize($this->user->tenant);

    $this->store = Store::factory()->create();

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('csv export returns downloadable file', function () {
    Order::factory()->count(2)->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.export.csv', $this->store));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename=orders-export-'.now()->format('Y-m-d').'.csv');
});

test('csv export contains order data and headers', function () {
    Order::factory()->create([
        'store_id' => $this->store->id,
        'order_number' => 'ORD-TEST-001',
        'customer_name' => 'Jane Export',
    ]);

    $response = $this->get(route('orders.export.csv', $this->store));
    $content = $response->streamedContent();

    expect($content)->toContain('Order Number')
        ->and($content)->toContain('ORD-TEST-001')
        ->and($content)->toContain('Jane Export');
});

test('csv export filters by status', function () {
    Order::factory()->confirmed()->create(['store_id' => $this->store->id]);
    Order::factory()->create(['store_id' => $this->store->id, 'status' => 'pending']);

    $response = $this->get(route('orders.export.csv', [$this->store, 'status' => 'confirmed']));
    $content = $response->streamedContent();

    expect($content)->toContain('confirmed')
        ->and($content)->not->toContain('pending');
});

test('csv export respects store scoping', function () {
    $store2 = Store::factory()->create();

    Order::factory()->create(['store_id' => $this->store->id, 'order_number' => 'STORE1-ORD']);
    Order::factory()->create(['store_id' => $store2->id, 'order_number' => 'STORE2-ORD']);

    $response = $this->get(route('orders.export.csv', $this->store));
    $content = $response->streamedContent();

    expect($content)->toContain('STORE1-ORD')
        ->and($content)->not->toContain('STORE2-ORD');
});
