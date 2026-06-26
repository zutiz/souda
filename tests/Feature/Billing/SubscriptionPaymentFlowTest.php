<?php

use App\Listeners\ProvisionTenantDatabase;
use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

const SSLC_VALIDATION_URL = 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php';

beforeEach(function () {
    $this->plan = Plan::factory()->create([
        'monthly_price' => 1000,
    ]);

    $this->tenant = Tenant::factory()->create();

    $this->subscription = Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
        'gateway' => 'sslcommerz',
        'status' => SubscriptionStatus::PendingPayment,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => $this->plan->monthly_price,
        'currency' => 'BDT',
        'starts_at' => now(),
        'expires_at' => null,
    ]);

    $this->transactionId = 'SSLC_TEST_'.uniqid();
    $this->valId = 'VAL_'.uniqid();

    $this->payment = Payment::create([
        'subscription_id' => $this->subscription->id,
        'tenant_id' => $this->tenant->id,
        'gateway' => 'sslcommerz',
        'transaction_id' => $this->transactionId,
        'amount' => $this->plan->monthly_price,
        'currency' => 'BDT',
        'status' => PaymentStatus::Pending,
        'payload' => [
            'val_id' => $this->valId,
        ],
    ]);
});

afterEach(function () {
    $this->payment->delete();
    $this->subscription->delete();
    $this->plan->delete();
    $this->tenant->delete();
    $this->tenant->forceDelete();
});

test('success callback processes payment and activates subscription', function () {
    Event::fake();
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => $this->transactionId,
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);

    Event::assertDispatched(PaymentReceived::class, function ($event) {
        return $event->payment->id === $this->payment->id;
    });

    Event::assertDispatched(SubscriptionActivated::class, function ($event) {
        return $event->subscription->id === $this->subscription->id;
    });
});

test('success callback with already completed payment redirects without re-processing', function () {
    $this->payment->markAsCompleted();

    Http::fake();
    Event::fake();

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));

    Http::assertNothingSent();
    Event::assertNotDispatched(PaymentReceived::class);
    Event::assertNotDispatched(SubscriptionActivated::class);
});

test('webhook processes payment and activates subscription', function () {
    Event::fake();
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => $this->transactionId,
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);

    $response = $this->post(route('billing.webhook.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $response->assertStatus(200);
    $response->assertSee('OK');

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);
});

test('webhook-first then success callback ordering — both succeed without duplicate processing', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => $this->transactionId,
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);
    Event::fake();

    $webhookResponse = $this->post(route('billing.webhook.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $webhookResponse->assertStatus(200);
    $webhookResponse->assertSee('OK');

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);

    $callbackResponse = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $callbackResponse->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);
});

test('success-callback-first then webhook ordering — both succeed without duplicate processing', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => $this->transactionId,
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);
    Event::fake();

    $callbackResponse = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $callbackResponse->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $webhookResponse = $this->post(route('billing.webhook.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $webhookResponse->assertStatus(200);
    $webhookResponse->assertSee('OK');

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);
});

test('success callback with no transaction ID still redirects to billing', function () {
    $response = $this->post(route('billing.success.sslcommerz'), []);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));
});

test('missing transaction ID in webhook returns 400', function () {
    $response = $this->post(route('billing.webhook.sslcommerz'), []);

    $response->assertStatus(400);
    $response->assertSee('Missing transaction ID');
});

test('success callback with invalid transaction ID redirects to billing', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => 'NONEXISTENT',
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);
    Event::fake();

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => 'NONEXISTENT_TXN',
        'val_id' => 'VAL_NONEXISTENT',
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));
});

test('invalid transaction ID in webhook returns 400', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => 'NONEXISTENT',
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);
    Event::fake();

    $response = $this->post(route('billing.webhook.sslcommerz'), [
        'tran_id' => 'NONEXISTENT_TXN',
        'val_id' => 'VAL_NONEXISTENT',
    ]);

    $response->assertStatus(400);
    $response->assertSee('Verification failed');
});

test('sslcommerz validation API failure completes locally on success callback', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'FAILED',
            'error' => 'Validation failed',
        ]),
    ]);
    Event::fake();

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => 'BAD_VAL_ID',
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);
});

test('sslcommerz validation API failure in webhook returns 400', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'FAILED',
            'error' => 'Validation failed',
        ]),
    ]);
    Event::fake();

    $response = $this->post(route('billing.webhook.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => 'BAD_VAL_ID',
    ]);

    $response->assertStatus(400);
    $response->assertSee('Verification failed');
});

test('HTTP failure from SSLCommerz validation API completes locally on success callback', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response('', 500),
    ]);
    Event::fake();

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);
});

test('success callback works even when ProvisionTenantDatabase listener throws', function () {
    Http::fake([
        SSLC_VALIDATION_URL => Http::response([
            'status' => 'VALID',
            'tran_id' => $this->transactionId,
            'amount' => 10.00,
            'currency' => 'BDT',
        ]),
    ]);

    Event::fake([
        PaymentReceived::class,
    ]);

    $mockProvisioner = Mockery::mock(ProvisionTenantDatabase::class);
    $mockProvisioner->shouldReceive('handle')
        ->once()
        ->andThrow(new RuntimeException('Database creation timed out'));

    $this->app->instance(ProvisionTenantDatabase::class, $mockProvisioner);

    $response = $this->post(route('billing.success.sslcommerz'), [
        'tran_id' => $this->transactionId,
        'val_id' => $this->valId,
    ]);

    $response->assertRedirect(route('billing', ['checkout' => 'success']));

    $this->assertDatabaseHas('billing_payments', [
        'id' => $this->payment->id,
        'status' => PaymentStatus::Completed,
    ]);

    $this->assertDatabaseHas('billing_subscriptions', [
        'id' => $this->subscription->id,
        'status' => SubscriptionStatus::Active,
    ]);

    Event::assertDispatched(PaymentReceived::class);
});
