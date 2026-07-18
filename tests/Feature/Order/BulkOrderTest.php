<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Store\Models\Store;
use App\Tenancy\TenantManager;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'orders.update']);
    Permission::firstOrCreate(['name' => 'orders.cancel']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('orders.update', 'orders.cancel');

    app(TenantManager::class)->initialize($this->user->tenant);

    $this->store = Store::factory()->create();

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('bulk status update changes multiple orders', function () {
    $orders = Order::factory()->count(3)->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.bulk.status', $this->store), [
        'order_ids' => $orders->pluck('id')->toArray(),
        'status' => 'confirmed',
    ]);

    $response->assertSessionHas('success');
    $orders->each(fn (Order $o) => expect($o->fresh()->status)->toBe('confirmed'));
});

test('bulk status update validates status', function () {
    $orders = Order::factory()->count(2)->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.bulk.status', $this->store), [
        'order_ids' => $orders->pluck('id')->toArray(),
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors(['status']);
});

test('bulk cancel cancels multiple orders', function () {
    $orders = Order::factory()->confirmed()->count(3)->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.bulk.cancel', $this->store), [
        'order_ids' => $orders->pluck('id')->toArray(),
        'reason' => 'Bulk cancellation',
    ]);

    $response->assertSessionHas('success');
    $orders->each(fn (Order $o) => expect($o->fresh()->status)->toBe('cancelled'));
});

test('bulk cancel requires reason', function () {
    $orders = Order::factory()->confirmed()->count(2)->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.bulk.cancel', $this->store), [
        'order_ids' => $orders->pluck('id')->toArray(),
    ]);

    $response->assertSessionHasErrors(['reason']);
});
