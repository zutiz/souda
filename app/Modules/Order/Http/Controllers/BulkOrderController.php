<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BulkOrderController
{
    use AuthorizesRequests;

    public function __construct(
        protected OrderService $orderService,
        protected StoreContextManager $storeContext,
    ) {}

    private function getStoreId(): string
    {
        $storeId = $this->storeContext->id();

        if ($storeId === null) {
            $defaultStore = Store::query()->default()->first();

            return $defaultStore?->id ?? '';
        }

        return $storeId;
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['string', 'exists:orders,id'],
            'status' => ['required', 'string', 'in:confirmed,processing,completed,cancelled,on_hold'],
        ]);

        $storeId = $this->getStoreId();
        $status = $request->input('status');
        $orderIds = $request->input('order_ids', []);
        $processed = 0;
        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = Order::where('store_id', $storeId)->find($orderId);

            if (! $order || ! Gate::allows('update', $order)) {
                $errors++;

                continue;
            }

            try {
                $this->orderService->updateStatus(
                    orderId: $order->id,
                    newStatus: $status,
                    changedBy: $request->user()?->id,
                );
                $processed++;
            } catch (\Throwable) {
                $errors++;
            }
        }

        $message = "{$processed} orders updated to {$status}.";
        if ($errors > 0) {
            $message .= " {$errors} orders skipped due to errors.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['string', 'exists:orders,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $storeId = $this->getStoreId();
        $reason = $request->input('reason');
        $orderIds = $request->input('order_ids', []);
        $processed = 0;
        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = Order::where('store_id', $storeId)->find($orderId);

            if (! $order || ! Gate::allows('cancel', $order)) {
                $errors++;

                continue;
            }

            try {
                $this->orderService->cancelOrder(
                    orderId: $order->id,
                    reason: $reason,
                    cancelledBy: $request->user()?->id,
                );
                $processed++;
            } catch (\Throwable) {
                $errors++;
            }
        }

        $message = "{$processed} orders cancelled.";
        if ($errors > 0) {
            $message .= " {$errors} orders skipped.";
        }

        return redirect()->back()->with('success', $message);
    }
}
