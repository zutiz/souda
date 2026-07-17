<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\AlertEngine;
use App\Modules\Inventory\Services\DashboardDataService;
use App\Modules\Inventory\Services\StockClassificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController
{
    public function __construct(
        protected AlertEngine $alertEngine,
        protected StockClassificationService $classificationService,
        protected DashboardDataService $dashboardDataService,
    ) {}

    public function dashboard(Request $request): Response
    {
        $days = (int) ($request->input('days', 30));
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null;

        $stats = $this->alertEngine->getDashboardStats();

        $classificationStats = $this->classificationService->getClassificationStats();

        $chartData = $this->dashboardDataService->getDashboardData($days, $warehouseId);

        $recentMovements = InventoryLedger::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (InventoryLedger $ledger) => [
                'id' => $ledger->id,
                'product_name' => $ledger->relationLoaded('product') && $ledger->product !== null
                    ? $ledger->product->name : $ledger->product_id,
                'sku' => $ledger->relationLoaded('product') && $ledger->product !== null
                    ? $ledger->product->sku : null,
                'warehouse_name' => $ledger->relationLoaded('warehouse') && $ledger->warehouse !== null
                    ? $ledger->warehouse->name : null,
                'quantity' => $ledger->quantity,
                'type' => $ledger->type,
                'created_at' => $ledger->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Inventory/Dashboard', [
            'stats' => $stats,
            'classificationStats' => $classificationStats,
            'recentMovements' => $recentMovements,
            'lowStockItems' => $this->alertEngine->findLowStock()->loadMissing(
                'product:id,name', 'warehouse:id,name',
            ),
            'chartData' => $chartData,
        ]);
    }

    public function index(): Response
    {
        $balances = InventoryBalance::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->orderBy('last_movement_at', 'desc')
            ->paginate(25);

        return Inertia::render('Inventory/Index', [
            'balances' => $balances,
        ]);
    }

    public function movements(): Response
    {
        $movements = InventoryLedger::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->latest()
            ->paginate(25);

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
        ]);
    }
}
