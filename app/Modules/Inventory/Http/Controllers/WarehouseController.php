<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\StoreWarehouseRequest;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController
{
    public function index(): Response
    {
        $warehouses = Warehouse::query()
            ->withCount('bins')
            ->latest()
            ->paginate(25);

        return Inertia::render('Inventory/Warehouses/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        Warehouse::create($request->validated());

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse): Response
    {
        $warehouse->load(['bins', 'outboundTransfers', 'inboundTransfers']);

        return Inertia::render('Inventory/Warehouses/Show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
