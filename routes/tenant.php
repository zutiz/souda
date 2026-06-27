<?php

declare(strict_types=1);

use App\Http\Controllers\BillingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Modules\Product\Http\Controllers\AttributeController;
use App\Modules\Product\Http\Controllers\BrandController;
use App\Modules\Product\Http\Controllers\CategoryController;
use App\Modules\Product\Http\Controllers\ProductController;
use App\Modules\Product\Http\Controllers\StockController;
use App\Modules\Store\Http\Controllers\StoreController;
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
    Route::middleware('subscription')->group(function () {

        // === Store Management (no store context) ===
        Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
        Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
        Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
        Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show');
        Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
        Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
        Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');
        Route::post('/stores/{store}/switch', [StoreController::class, 'switch'])->name('stores.switch');
        Route::post('/stores/{store}/set-default', [StoreController::class, 'setDefault'])->name('stores.set-default');

        // === Existing routes (outside store context) ===
        Route::get('/dashboard', function () {
            return Inertia::render('dashboard');
        })->name('dashboard');

        Route::resource('tasks', TaskController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Product sub-resources — must be before products resource to prevent 405
        Route::prefix('products')->group(function () {
            // Categories
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

            // Brands
            Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
            Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
            Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
            Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

            // Attributes
            Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
            Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
            Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
            Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
            Route::post('/attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
            Route::put('/attributes/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
            Route::delete('/attributes/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

            // Inventory
            Route::get('/inventory', [StockController::class, 'lowStock'])->name('inventory.index');

            // Stock Transfers
            Route::get('/stock-transfers', [StockController::class, 'movements'])->name('stock-transfers.index');
            Route::post('/stock-transfers', [StockController::class, 'transfer'])->name('stock-transfers.transfer');

            // Stock movements (redirect target for receive/deduct/adjust)
            Route::get('/movements', [StockController::class, 'movements'])->name('stock.movements');
        });

        Route::resource('products', ProductController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite')->middleware('seat');
        Route::post('/team/accept/{token}', [TeamController::class, 'accept'])->name('team.accept');
        Route::delete('/team/{allocation}', [TeamController::class, 'destroy'])->name('team.destroy');
        Route::post('/team/{allocation}/resend', [TeamController::class, 'resend'])->name('team.resend');
    });
});

// === Store-scoped routes ===
Route::middleware([
    'web', 'auth', InitializeTenancyByUser::class,
    'store.context', 'subscription',
])->prefix('{store}')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard');
    })->name('store.dashboard');
});

// SSLCommerz callback - no auth required (external POST from payment gateway + browser GET redirect after payment)
Route::match(['get', 'post'], '/billing/success/sslcommerz', [BillingController::class, 'sslcommerzSuccess'])->name('billing.success.sslcommerz');
Route::post('/billing/webhook/sslcommerz', [BillingController::class, 'sslcommerzWebhook'])->name('billing.webhook.sslcommerz');
