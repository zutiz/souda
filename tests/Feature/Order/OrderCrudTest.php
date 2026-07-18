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

test('index returns paginated orders list via inertia', function () {
    Order::factory()->count(3)->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.index', $this->store));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Order/Index')
        ->has('orders', 3)
    );
});

test('index filters orders by status', function () {
    Order::factory()->confirmed()->create(['store_id' => $this->store->id]);
    Order::factory()->create(['store_id' => $this->store->id, 'status' => 'pending']);

    $response = $this->get(route('orders.index', [
        $this->store,
        'status' => 'confirmed',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Order/Index')
        ->has('orders', 1)
    );
});

test('order requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('orders.index', $this->store));

    $response->assertRedirect(route('login'));
});

test('store returns inertia show page', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.show', [$this->store, $order]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Order/Show')
        ->where('order.id', $order->id)
    );
});

test('cancel order updates status and redirects', function () {
    $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.cancel', [$this->store, $order]), [
        'reason' => 'Customer request',
    ]);

    $response->assertSessionHas('success');
    expect($order->fresh()->status)->toBe('cancelled');
});

test('cancel order requires a reason', function () {
    $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

    $response = $this->post(route('orders.cancel', [$this->store, $order]), []);

    $response->assertSessionHasErrors(['reason']);
});

test('update status changes order status and redirects', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->put(route('orders.update-status', [$this->store, $order]), [
        'status' => 'confirmed',
        'reason' => 'Payment received',
    ]);

    $response->assertSessionHas('success');
    expect($order->fresh()->status)->toBe('confirmed');
});

test('update status validates allowed transitions', function () {
    $order = Order::factory()->cancelled()->create(['store_id' => $this->store->id]);

    $response = $this->put(route('orders.update-status', [$this->store, $order]), [
        'status' => 'confirmed',
        'reason' => 'Reactivate',
    ]);

    $response->assertSessionHasErrors(['status']);
});

test('timeline returns order events', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.timeline', [$this->store, $order]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Order/Timeline')
        ->has('events')
    );
});
