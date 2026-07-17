<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\UpdateSuggestionRequest;
use App\Modules\Inventory\Models\PurchaseSuggestion;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\ReorderEngine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController
{
    public function __construct(
        protected ReorderEngine $reorderEngine,
    ) {}

    public function index(): Response
    {
        $suggestions = PurchaseSuggestion::with([
            'product:id,name,sku,lead_time_days,safety_stock',
            'warehouse:id,name',
        ])
            ->latest()
            ->paginate(25);

        $stats = [
            'total_pending' => PurchaseSuggestion::where('status', 'pending')->count(),
            'total_ordered' => PurchaseSuggestion::where('status', 'ordered')->count(),
            'total_products' => PurchaseSuggestion::where('status', 'pending')
                ->distinct('product_id')->count('product_id'),
        ];

        $warehouses = Warehouse::active()->get(['id', 'name']);

        return Inertia::render('Inventory/Suggestions/Index', [
            'suggestions' => $suggestions,
            'stats' => $stats,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(UpdateSuggestionRequest $request, PurchaseSuggestion $suggestion): RedirectResponse
    {
        $status = $request->input('status');

        if ($status === 'ordered') {
            $this->reorderEngine->markOrdered(
                $suggestion,
                $request->input('order_reference'),
            );
        } elseif ($status === 'dismissed') {
            $this->reorderEngine->dismiss(
                $suggestion,
                $request->input('notes'),
            );
        }

        return redirect()->route('inventory.suggestions.index')
            ->with('success', "Suggestion {$status} successfully.");
    }

    public function generate(): RedirectResponse
    {
        $count = $this->reorderEngine->generateSuggestions();

        return redirect()->route('inventory.suggestions.index')
            ->with('success', "Generated {$count} purchase suggestion(s).");
    }
}
