<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\WarehouseDTO;
use App\Modules\Product\Http\Requests\StoreWarehouseRequest;
use App\Modules\Product\Models\Warehouse;
use App\Modules\Product\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController
{
    public function __construct(
        protected WarehouseService $warehouseService,
    ) {}

    public function index(): Response
    {
        $warehouses = Warehouse::query()->orderBy('name')->paginate(25);

        return Inertia::render('Product/Warehouse/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $dto = WarehouseDTO::fromRequest($request->validated());
        $this->warehouseService->createWarehouse($dto);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $dto = WarehouseDTO::fromRequest($request->validated());
        $this->warehouseService->updateWarehouse($warehouse, $dto);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->warehouseService->deleteWarehouse($warehouse);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    public function setDefault(Warehouse $warehouse): RedirectResponse
    {
        $this->warehouseService->setDefaultWarehouse($warehouse);

        return redirect()->route('warehouses.index')
            ->with('success', 'Default warehouse set successfully.');
    }
}
