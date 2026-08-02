<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\OrderReturn;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReturnController
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $returns = OrderReturn::query()
            ->with('order')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Order/Returns/Index', [
            'returns' => $returns,
            'filters' => $request->only('status'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Order/Returns/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
            'reason' => ['required', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        OrderReturn::query()->create([
            'order_id' => $validated['order_id'],
            'reason' => $validated['reason'],
            'items' => $validated['items'] ?? [],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'processed_by' => $request->user()?->id,
        ]);

        return redirect()->route('orders.returns.index')
            ->with('success', 'Return request created successfully.');
    }

    public function show(OrderReturn $return)
    {
        $return->load('order');

        return Inertia::render('Order/Returns/Show', [
            'return' => $return,
        ]);
    }
}
