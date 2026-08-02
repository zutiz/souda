<?php

declare(strict_types=1);

use App\Http\Controllers\BillingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Modules\Inventory\Http\Controllers\AlertController;
use App\Modules\Inventory\Http\Controllers\BatchController;
use App\Modules\Inventory\Http\Controllers\CountController;
use App\Modules\Inventory\Http\Controllers\DashboardExportController;
use App\Modules\Inventory\Http\Controllers\ForecastController;
use App\Modules\Inventory\Http\Controllers\InventoryController;
use App\Modules\Inventory\Http\Controllers\OperationsController;
use App\Modules\Inventory\Http\Controllers\ReservationController;
use App\Modules\Inventory\Http\Controllers\RuleController;
use App\Modules\Inventory\Http\Controllers\SerialNumberController;
use App\Modules\Inventory\Http\Controllers\StockClassificationController;
use App\Modules\Inventory\Http\Controllers\SuggestionController;
use App\Modules\Inventory\Http\Controllers\TransferController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
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

        // === Inventory Module ===
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'dashboard'])->name('dashboard');
            Route::get('/balances', [InventoryController::class, 'index'])->name('index');
            Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');

            // Warehouses
            Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
            Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
            Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

            // Transfers
            Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
            Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
            Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
            Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
            Route::post('/transfers/{transfer}/send', [TransferController::class, 'send'])->name('transfers.send');
            Route::post('/transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('transfers.receive');
            Route::post('/transfers/{transfer}/cancel', [TransferController::class, 'cancel'])->name('transfers.cancel');

            // Alerts
            Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');
            Route::post('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
            Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

            // Stock Classification
            Route::get('/classification', [StockClassificationController::class, 'index'])->name('classification.index');
            Route::post('/classification/refresh', [StockClassificationController::class, 'refresh'])->name('classification.refresh');

            // Demand Forecasts
            Route::get('/forecasts', [ForecastController::class, 'index'])->name('forecasts.index');
            Route::get('/forecasts/{forecast}', [ForecastController::class, 'show'])->name('forecasts.show');
            Route::post('/forecasts/generate', [ForecastController::class, 'generate'])->name('forecasts.generate');
            Route::post('/forecasts/resolve', [ForecastController::class, 'resolve'])->name('forecasts.resolve');

            // Automation Rules
            Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
            Route::get('/rules/create', [RuleController::class, 'create'])->name('rules.create');
            Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');
            Route::get('/rules/{rule}', [RuleController::class, 'show'])->name('rules.show');
            Route::put('/rules/{rule}', [RuleController::class, 'update'])->name('rules.update');
            Route::post('/rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('rules.toggle');
            Route::post('/rules/{rule}/evaluate', [RuleController::class, 'evaluate'])->name('rules.evaluate');
            Route::delete('/rules/{rule}', [RuleController::class, 'destroy'])->name('rules.destroy');

            // Physical Counts / Cycle Counting
            Route::get('/counts', [CountController::class, 'index'])->name('counts.index');
            Route::get('/counts/create', [CountController::class, 'create'])->name('counts.create');
            Route::post('/counts', [CountController::class, 'store'])->name('counts.store');
            Route::get('/counts/{count}', [CountController::class, 'show'])->name('counts.show');
            Route::post('/counts/{count}/record', [CountController::class, 'recordCounts'])->name('counts.record');
            Route::post('/counts/{count}/verify', [CountController::class, 'verify'])->name('counts.verify');
            Route::post('/counts/{count}/apply', [CountController::class, 'applyAdjustments'])->name('counts.apply');
            Route::post('/counts/{count}/complete', [CountController::class, 'complete'])->name('counts.complete');
            Route::post('/counts/{count}/cancel', [CountController::class, 'cancel'])->name('counts.cancel');

            // Purchase Suggestions
            Route::get('/suggestions', [SuggestionController::class, 'index'])->name('suggestions.index');
            Route::put('/suggestions/{suggestion}', [SuggestionController::class, 'update'])->name('suggestions.update');
            Route::post('/suggestions/generate', [SuggestionController::class, 'generate'])->name('suggestions.generate');

            // Dashboard Export
            Route::get('/dashboard/export/csv', [DashboardExportController::class, 'csv'])->name('dashboard.export.csv');

            // Batches
            Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
            Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
            Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
            Route::post('/batches/{batch}/deduct', [BatchController::class, 'deduct'])->name('batches.deduct');
            Route::post('/batches/{batch}/quarantine', [BatchController::class, 'quarantine'])->name('batches.quarantine');

            // Serial Numbers
            Route::get('/serials', [SerialNumberController::class, 'index'])->name('serials.index');
            Route::post('/serials', [SerialNumberController::class, 'store'])->name('serials.store');
            Route::post('/serials/batch', [SerialNumberController::class, 'storeBatch'])->name('serials.store-batch');
            Route::get('/serials/{serial}', [SerialNumberController::class, 'show'])->name('serials.show');
            Route::post('/serials/{serial}/sold', [SerialNumberController::class, 'markSold'])->name('serials.mark-sold');
            Route::post('/serials/{serial}/return', [SerialNumberController::class, 'markReturned'])->name('serials.mark-returned');

            // Reservations
            Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
            Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
            Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

            // Operations / Schedule Monitor
            Route::get('/operations', [OperationsController::class, 'index'])->name('operations');
            Route::post('/operations/{command}/run', [OperationsController::class, 'run'])->name('operations.run');
        });

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

    // Order module routes (store-specific)
    require __DIR__.'/order.php';
});

// === Order routes (no store prefix) ===
Route::middleware(['web', 'auth', InitializeTenancyByUser::class, 'subscription'])->group(function () {
    require __DIR__.'/order.php';
});

// SSLCommerz callback - no auth required (external POST from payment gateway + browser GET redirect after payment)
Route::match(['get', 'post'], '/billing/success/sslcommerz', [BillingController::class, 'sslcommerzSuccess'])->name('billing.success.sslcommerz');
Route::post('/billing/webhook/sslcommerz', [BillingController::class, 'sslcommerzWebhook'])->name('billing.webhook.sslcommerz');
