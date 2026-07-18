<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Services\RefundService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefundController
{
    use AuthorizesRequests;

    public function __construct(
        protected RefundService $refundService,
    ) {}

    public function refund(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->refundService->refundOrder(
            orderId: $order->id,
            amount: $validated['amount'],
            reason: $validated['reason'],
            refundedBy: $request->user()?->id,
        );

        return redirect()->back()->with('success', 'Refund processed successfully.');
    }

    public function refundItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->refundService->refundItem(
            orderId: $order->id,
            orderItemId: $item->id,
            amount: $validated['amount'],
            reason: $validated['reason'],
            refundedBy: $request->user()?->id,
        );

        return redirect()->back()->with('success', 'Item refunded successfully.');
    }
}
