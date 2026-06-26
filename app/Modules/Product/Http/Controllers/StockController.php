<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\Http\Requests\StockAdjustmentRequest;
use App\Modules\Product\Http\Requests\StockTransferRequest;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Models\WarehouseStock;
use App\Modules\Product\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController
{
    public function __construct(
        protected StockService $stockService,
    ) {}

    public function index(Product $product): Response
    {
        $product->load(['warehouseStock.warehouse']);

        return Inertia::render('Product/Stock/Index', [
            'product' => $product,
        ]);
    }

    public function variantStock(Product $product, Variant $variant): Response
    {
        $variant->load(['warehouseStock.warehouse']);

        return Inertia::render('Product/Stock/Variant', [
            'product' => $product,
            'variant' => $variant,
        ]);
    }

    public function receive(StockAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->stockService->receiveStock(
            warehouseId: (int) $data['warehouse_id'],
            productId: $data['product_id'] ?? null,
            variantId: $data['variant_id'] ?? null,
            quantity: (int) $data['quantity'],
            notes: $data['notes'] ?? null,
        );

        return redirect()->route('stock.movements')
            ->with('success', 'Stock received successfully.');
    }

    public function deduct(StockAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->stockService->deductStock(
            warehouseId: (int) $data['warehouse_id'],
            productId: $data['product_id'] ?? null,
            variantId: $data['variant_id'] ?? null,
            quantity: (int) $data['quantity'],
            notes: $data['notes'] ?? null,
        );

        return redirect()->route('stock.movements')
            ->with('success', 'Stock deducted successfully.');
    }

    public function adjust(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required_without:variant_id', 'exists:products,id'],
            'variant_id' => ['required_without:product_id', 'exists:variants,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->stockService->adjustStock(
            warehouseId: (int) $request->input('warehouse_id'),
            productId: $request->input('product_id'),
            variantId: $request->input('variant_id'),
            newQuantity: (int) $request->input('new_quantity'),
            notes: $request->input('notes'),
        );

        return redirect()->route('stock.movements')
            ->with('success', 'Stock adjusted successfully.');
    }

    public function transfer(StockTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->stockService->transferStock(
            fromWarehouseId: (int) $data['from_warehouse_id'],
            toWarehouseId: (int) $data['to_warehouse_id'],
            productId: $data['product_id'] ?? null,
            variantId: $data['variant_id'] ?? null,
            quantity: (int) $data['quantity'],
            notes: $data['notes'] ?? null,
        );

        return redirect()->route('stock.movements')
            ->with('success', 'Stock transferred successfully.');
    }

    public function movements(Request $request): Response
    {
        $movements = $this->stockService->getMovementHistory(
            productId: $request->input('product_id'),
            variantId: $request->input('variant_id'),
            warehouseId: $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null,
        );

        return Inertia::render('Product/Stock/Movements', [
            'movements' => $movements,
        ]);
    }

    public function lowStock(): Response
    {
        $stocks = WarehouseStock::query()
            ->with(['product', 'variant', 'warehouse'])
            ->whereRaw('(quantity - reserved_quantity) <= reorder_level')
            ->whereRaw('(quantity - reserved_quantity) > 0')
            ->paginate(25);

        return Inertia::render('Product/Stock/LowStock', [
            'stocks' => $stocks,
        ]);
    }
}
