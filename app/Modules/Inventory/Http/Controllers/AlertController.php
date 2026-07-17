<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Inventory\Services\AlertEngine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AlertController
{
    public function __construct(
        protected AlertEngine $alertEngine,
    ) {}

    public function index(): Response
    {
        $lowStock = $this->alertEngine->findLowStock()->loadMissing(
            'product:id,name,sku,low_stock_threshold',
            'warehouse:id,name',
        );

        $deadStock = $this->alertEngine->findDeadStock(days: 90)->loadMissing(
            'product:id,name,sku',
            'warehouse:id,name',
        );

        $overstock = $this->alertEngine->findOverstock(threshold: 1000)->loadMissing(
            'product:id,name,sku',
            'warehouse:id,name',
        );

        $persistentAlerts = InventoryAlert::with(['rule:id,name'])
            ->active()
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Inventory/Alerts', [
            'lowStock' => $lowStock,
            'deadStock' => $deadStock,
            'overstock' => $overstock,
            'persistentAlerts' => $persistentAlerts,
        ]);
    }

    public function dismiss(InventoryAlert $alert): RedirectResponse
    {
        $alert->dismiss();

        return redirect()->back()->with('success', 'Alert dismissed.');
    }

    public function resolve(InventoryAlert $alert): RedirectResponse
    {
        $alert->resolve();

        return redirect()->back()->with('success', 'Alert resolved.');
    }
}
