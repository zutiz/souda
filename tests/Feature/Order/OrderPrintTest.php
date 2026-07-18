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

test('thermal receipt returns plaintext', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.print.thermal', [$this->store, $order]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'inline; filename="receipt-'.$order->order_number.'.txt"');
});

test('thermal receipt contains order number', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.print.thermal', [$this->store, $order]));

    $response->assertSeeText($order->order_number);
});

test('invoice returns html', function () {
    $order = Order::factory()->create(['store_id' => $this->store->id]);

    $response = $this->get(route('orders.print.invoice', [$this->store, $order]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
});
