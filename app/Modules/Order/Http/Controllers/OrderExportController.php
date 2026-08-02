<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\Order;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderExportController
{
    public function __construct(
        protected StoreContextManager $storeContext,
    ) {}

    public function csv(Request $request): Response
    {
        $storeId = $this->storeContext->id();

        if ($storeId === null) {
            $defaultStore = Store::query()->default()->first();
            $storeId = $defaultStore?->id ?? '';
        }

        $query = Order::with('items');

        if (! empty($storeId)) {
            $query->where('store_id', $storeId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->input('order_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->input('date_to'));
        }

        $query->orderBy('created_at', 'desc');
        $orders = $query->get();

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, [
            'Order Number', 'Status', 'Type', 'Customer Name', 'Customer Phone',
            'Customer Email', 'Subtotal', 'Shipping', 'Tax', 'Discount',
            'Grand Total', 'Paid', 'Due', 'Currency', 'Payment Method',
            'Payment Status', 'Items Count', 'Notes', 'Placed At',
        ]);

        foreach ($orders as $order) {
            fputcsv($csv, [
                $order->order_number,
                $order->status,
                $order->order_type,
                $order->customer_name,
                $order->customer_phone,
                $order->customer_email,
                $order->subtotal / 100,
                $order->shipping_total / 100,
                $order->tax_total / 100,
                $order->discount_total / 100,
                $order->grand_total / 100,
                $order->paid_total / 100,
                $order->due_total / 100,
                $order->currency,
                $order->payment_method,
                $order->payment_status,
                $order->items->count(),
                $order->notes,
                $order->placed_at?->toDateTimeString(),
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="orders-export-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}
