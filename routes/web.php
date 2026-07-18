<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\TenantSwitcherController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social-auth.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social-auth.callback');

// Billing routes — no tenant DB required (billing lives in central DB).
// The tenant DB is provisioned on subscription activation.
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/callback/{gateway}', [BillingController::class, 'callback'])->name('billing.callback');

    // Tenant switching — must be before tenancy initialization
    Route::post('/tenant/switch', [TenantSwitcherController::class, 'switch'])->name('tenant.switch');
    Route::get('/tenant/create', [TenantSwitcherController::class, 'create'])->name('tenant.create');
    Route::post('/tenant', [TenantSwitcherController::class, 'store'])->name('tenant.store');
});

require __DIR__.'/settings.php';

// Public tracking — no auth required
use App\Modules\Order\Http\Controllers\TrackingController;

Route::get('/tracking/{trackingNumber}', [TrackingController::class, 'show'])->name('tracking.show');

// Courier webhooks — no auth, external POST
Route::post('/webhook/courier/{courier}', [TrackingController::class, 'webhook'])->name('webhook.courier');
