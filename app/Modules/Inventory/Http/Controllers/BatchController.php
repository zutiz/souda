<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Services\BatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BatchController
{
    public function __construct(
        protected BatchService $batchService,
    ) {}

    public function index(Request $request): Response
    {
        $query = InventoryBatch::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhere('supplier_batch', 'like', "%{$search}%");
            });
        }

        $batches = $query->paginate(25);

        return Inertia::render('Inventory/Batches/Index', [
            'batches' => $batches,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(InventoryBatch $batch): Response
    {
        $batch->load(['product:id,name,sku', 'warehouse:id,name', 'serialNumbers']);

        return Inertia::render('Inventory/Batches/Show', [
            'batch' => $batch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'warehouse_id' => ['required', 'integer', 'exists:inventory_warehouses,id'],
            'batch_number' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'variant_id' => ['nullable', 'string'],
            'supplier_batch' => ['nullable', 'string', 'max:100'],
            'manufacturing_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'best_before' => ['nullable', 'date'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->batchService->receive(
            productId: $validated['product_id'],
            warehouseId: (int) $validated['warehouse_id'],
            batchNumber: $validated['batch_number'],
            quantity: (int) $validated['quantity'],
            variantId: $validated['variant_id'] ?? null,
            supplierBatch: $validated['supplier_batch'] ?? null,
            manufacturingDate: $validated['manufacturing_date'] ?? null,
            expiryDate: $validated['expiry_date'] ?? null,
            bestBefore: $validated['best_before'] ?? null,
            unitCost: (int) ($validated['unit_cost'] ?? 0),
        );

        return redirect()->route('inventory.batches.index')
            ->with('success', 'Batch received successfully.');
    }

    public function deduct(Request $request, InventoryBatch $batch): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->batchService->deduct(
                productId: $batch->product_id,
                warehouseId: $batch->warehouse_id,
                batchNumber: $batch->batch_number,
                quantity: (int) $validated['quantity'],
                variantId: $batch->variant_id,
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function quarantine(InventoryBatch $batch): RedirectResponse
    {
        $this->batchService->quarantine($batch->id);

        return redirect()->route('inventory.batches.show', $batch)
            ->with('success', 'Batch quarantined.');
    }
}
