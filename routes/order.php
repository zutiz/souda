<?php

declare(strict_types=1);

use App\Modules\Order\Http\Controllers\BulkOrderController;
use App\Modules\Order\Http\Controllers\OrderController;
use App\Modules\Order\Http\Controllers\OrderExportController;
use App\Modules\Order\Http\Controllers\OrderPrintController;
use App\Modules\Order\Http\Controllers\RefundController;
use App\Modules\Order\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order Module Routes
|--------------------------------------------------------------------------
|
| Included from tenant.php store-scoped group. No prefix or middleware
| here — the parent group provides {store}, store.context, and subscription.
|
*/

// Orders
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

// Order print
Route::get('/orders/{order}/print/thermal', [OrderPrintController::class, 'thermal'])->name('orders.print.thermal');
Route::get('/orders/{order}/print/invoice', [OrderPrintController::class, 'invoice'])->name('orders.print.invoice');

// Order timeline
Route::get('/orders/{order}/timeline', [OrderController::class, 'timeline'])->name('orders.timeline');

// Order refund
Route::post('/orders/{order}/refund', [RefundController::class, 'refund'])->name('orders.refund');
Route::post('/orders/{order}/items/{item}/refund', [RefundController::class, 'refundItem'])->name('orders.items.refund');

// Order export
Route::get('/orders/export/csv', [OrderExportController::class, 'csv'])->name('orders.export.csv');

// Bulk operations
Route::post('/orders/bulk/status', [BulkOrderController::class, 'updateStatus'])->name('orders.bulk.status');
Route::post('/orders/bulk/cancel', [BulkOrderController::class, 'cancel'])->name('orders.bulk.cancel');

// Shipments nested under orders
Route::get('/orders/{order}/shipments', [ShipmentController::class, 'index'])->name('orders.shipments.index');
Route::get('/orders/{order}/shipments/create', [ShipmentController::class, 'create'])->name('orders.shipments.create');
Route::post('/orders/{order}/shipments', [ShipmentController::class, 'store'])->name('orders.shipments.store');
Route::get('/orders/{order}/shipments/{shipment}', [ShipmentController::class, 'show'])->name('orders.shipments.show');
Route::get('/orders/{order}/shipments/{shipment}/track', [ShipmentController::class, 'track'])->name('orders.shipments.track');
