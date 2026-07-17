<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\RecordCountItemsRequest;
use App\Modules\Inventory\Http\Requests\StoreCountRequest;
use App\Modules\Inventory\Models\InventoryCount;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\CountEngine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CountController
{
    public function __construct(
        protected CountEngine $countEngine,
    ) {}

    public function index(): Response
    {
        $counts = InventoryCount::with(['warehouse:id,name', 'countedByUser:id,name'])
            ->withCount('items')
            ->latest()
            ->paginate(25);

        return Inertia::render('Inventory/Counts/Index', [
            'counts' => $counts,
        ]);
    }

    public function create(): Response
    {
        $warehouses = Warehouse::active()->get(['id', 'name']);

        return Inertia::render('Inventory/Counts/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreCountRequest $request): RedirectResponse
    {
        $count = $this->countEngine->createCount(
            warehouseId: $request->integer('warehouse_id'),
            type: $request->input('type'),
            countedBy: $request->user()?->id,
            productIds: $request->input('product_ids'),
        );

        return redirect()->route('inventory.counts.show', $count)
            ->with('success', 'Count created successfully.');
    }

    public function show(InventoryCount $count): Response
    {
        $count->load([
            'warehouse:id,name',
            'items.product:id,name,sku',
            'items.bin:id,code',
            'countedByUser:id,name',
            'verifiedByUser:id,name',
        ]);

        return Inertia::render('Inventory/Counts/Show', [
            'count' => $count,
        ]);
    }

    public function recordCounts(RecordCountItemsRequest $request, InventoryCount $count): RedirectResponse
    {
        $this->countEngine->recordCounts($count, $request->input('items'));

        return redirect()->route('inventory.counts.show', $count)
            ->with('success', 'Physical counts recorded.');
    }

    public function verify(InventoryCount $count): RedirectResponse
    {
        $this->countEngine->verifyCount($count, request()->user()->id);

        return redirect()->route('inventory.counts.show', $count)
            ->with('success', 'Count verified successfully.');
    }

    public function applyAdjustments(InventoryCount $count): RedirectResponse
    {
        $adjusted = $this->countEngine->applyAdjustments($count);

        return redirect()->route('inventory.counts.show', $count)
            ->with('success', "Adjustments applied for {$adjusted} item(s).");
    }

    public function complete(InventoryCount $count): RedirectResponse
    {
        $this->countEngine->completeCount($count);

        return redirect()->route('inventory.counts.show', $count)
            ->with('success', 'Count completed.');
    }

    public function cancel(InventoryCount $count): RedirectResponse
    {
        $this->countEngine->cancelCount($count);

        return redirect()->route('inventory.counts.index')
            ->with('success', 'Count cancelled.');
    }
}
