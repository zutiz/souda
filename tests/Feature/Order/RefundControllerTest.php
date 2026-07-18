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

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('orders.update');

    app(TenantManager::class)->initialize($this->user->tenant);

    $this->store = Store::factory()->create();

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('refund processes partial refund on paid order', function () {
    $order = Order::factory()->create([
        'store_id' => $this->store->id,
        'status' => 'confirmed',
        'grand_total' => 10000,
        'paid_total' => 10000,
        'refund_total' => 0,
        'due_total' => 0,
    ]);

    $response = $this->post(route('orders.refund', [$this->store, $order]), [
        'amount' => 2000,
        'reason' => 'Customer returned item',
    ]);

    $response->assertSessionHas('success');
    expect((int) $order->fresh()->refund_total)->toBe(2000)
        ->and($order->fresh()->payment_status)->toBe('partially_refunded');
});

test('refund validates amount field', function () {
    $order = Order::factory()->create([
        'store_id' => $this->store->id,
        'grand_total' => 10000,
        'paid_total' => 10000,
    ]);

    $response = $this->post(route('orders.refund', [$this->store, $order]), [
        'amount' => -100,
        'reason' => 'Test',
    ]);

    $response->assertSessionHasErrors(['amount']);
});

test('refund item processes per-item refund', function () {
    $order = Order::factory()->create([
        'store_id' => $this->store->id,
        'status' => 'confirmed',
        'grand_total' => 10000,
        'paid_total' => 10000,
    ]);
    $item = $order->items()->create([
        'name' => 'Refundable Item',
        'total_price' => 5000,
        'quantity' => 1,
        'unit_price' => 5000,
    ]);

    $response = $this->post(route('orders.items.refund', [$this->store, $order, 'item' => $item->id]), [
        'amount' => 2500,
        'reason' => 'Partial item refund',
    ]);

    $response->assertSessionHas('success');
    expect((int) $order->fresh()->refund_total)->toBe(2500);
});
