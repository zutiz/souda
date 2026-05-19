<?php

declare(strict_types=1);

use App\Http\Controllers\BillingController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\InitializeTenancyByUser;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Routes that require an authenticated user with an active tenant.
| The InitializeTenancyByUser middleware sets the tenant context
| based on the authenticated user's tenant_id.
|
*/

Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/callback/{gateway}', [BillingController::class, 'callback'])->name('billing.callback');

    Route::middleware('subscription')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('dashboard');
        })->name('dashboard');

        Route::resource('tasks', TaskController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});

// SSLCommerz callback - no auth required (external POST from payment gateway)
Route::post('/billing/success/sslcommerz', [BillingController::class, 'sslcommerzSuccess'])->name('billing.success.sslcommerz');
Route::post('/billing/webhook/sslcommerz', [BillingController::class, 'sslcommerzWebhook'])->name('billing.webhook.sslcommerz');
