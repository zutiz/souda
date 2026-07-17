<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockClassificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockClassificationController
{
    public function __construct(
        protected StockClassificationService $classificationService,
    ) {}

    public function index(Request $request): Response
    {
        $stats = $this->classificationService->getClassificationStats();

        $balances = $this->classificationService->getClassifiedBalances(
            abcClass: $request->input('abc_class'),
            velocityClass: $request->input('velocity_class'),
            warehouseId: $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null,
            search: $request->input('search'),
        );

        $warehouses = Warehouse::active()->get(['id', 'name']);

        return Inertia::render('Inventory/Rules/Classification', [
            'stats' => $stats,
            'balances' => $balances,
            'warehouses' => $warehouses,
            'filters' => [
                'abc_class' => $request->input('abc_class'),
                'velocity_class' => $request->input('velocity_class'),
                'warehouse_id' => $request->input('warehouse_id'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function refresh(?int $warehouseId = null): RedirectResponse
    {
        $this->classificationService->classifyAll($warehouseId);

        return redirect()->route('inventory.classification.index')
            ->with('success', 'Stock classification refreshed.');
    }
}
