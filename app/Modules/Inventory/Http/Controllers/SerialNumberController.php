<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Inventory\Services\SerialNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SerialNumberController
{
    public function __construct(
        protected SerialNumberService $serialService,
    ) {}

    public function index(Request $request): Response
    {
        $query = SerialNumber::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%");
            });
        }

        $serials = $query->paginate(25);

        return Inertia::render('Inventory/Serials/Index', [
            'serials' => $serials,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(SerialNumber $serial): Response
    {
        $serial->load(['product:id,name,sku', 'warehouse:id,name', 'batch:id,batch_number']);

        return Inertia::render('Inventory/Serials/Show', [
            'serial' => $serial,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'serial_number' => ['required', 'string', 'max:200'],
            'variant_id' => ['nullable', 'string'],
            'warehouse_id' => ['nullable', 'integer', 'exists:inventory_warehouses,id'],
            'batch_id' => ['nullable', 'integer', 'exists:inventory_batches,id'],
            'warranty_expires_at' => ['nullable', 'date'],
        ]);

        $this->serialService->register(
            productId: $validated['product_id'],
            serialNumber: $validated['serial_number'],
            variantId: $validated['variant_id'] ?? null,
            warehouseId: $validated['warehouse_id'] ?? null,
            batchId: isset($validated['batch_id']) ? (int) $validated['batch_id'] : null,
            warrantyExpiresAt: $validated['warranty_expires_at'] ?? null,
        );

        return redirect()->route('inventory.serials.index')
            ->with('success', 'Serial number registered.');
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'serial_numbers' => ['required', 'array', 'min:1'],
            'serial_numbers.*' => ['required', 'string', 'max:200'],
            'variant_id' => ['nullable', 'string'],
            'warehouse_id' => ['nullable', 'integer', 'exists:inventory_warehouses,id'],
            'batch_id' => ['nullable', 'integer', 'exists:inventory_batches,id'],
        ]);

        $this->serialService->registerBatch(
            productId: $validated['product_id'],
            serialNumbers: $validated['serial_numbers'],
            variantId: $validated['variant_id'] ?? null,
            warehouseId: $validated['warehouse_id'] ?? null,
            batchId: isset($validated['batch_id']) ? (int) $validated['batch_id'] : null,
        );

        return redirect()->route('inventory.serials.index')
            ->with('success', count($validated['serial_numbers']).' serial numbers registered.');
    }

    public function markSold(Request $request, SerialNumber $serial): JsonResponse
    {
        $validated = $request->validate([
            'order_reference' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->serialService->markAsSold(
                serialNumber: $serial->serial_number,
                productId: $serial->product_id,
                orderReference: $validated['order_reference'],
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function markReturned(SerialNumber $serial): JsonResponse
    {
        try {
            $this->serialService->markAsReturned(
                serialNumber: $serial->serial_number,
                productId: $serial->product_id,
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
