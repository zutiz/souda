<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\A4InvoiceRenderer;
use App\Modules\Order\Services\OrderService;
use App\Modules\Order\Services\ThermalReceiptRenderer;
use App\Modules\Store\Models\Store;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrderPrintController
{
    use AuthorizesRequests;

    public function __construct(
        protected OrderService $orderService,
        protected ThermalReceiptRenderer $thermalRenderer,
        protected A4InvoiceRenderer $a4Renderer,
    ) {}

    public function thermal(Order $order): Response
    {
        Gate::authorize('view', $order);

        $dto = $this->orderService->getOrder($order->id);
        $store = Store::find($order->store_id);

        $content = $this->thermalRenderer->render($dto, [
            'store_name' => $store?->name,
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline; filename="receipt-'.$order->order_number.'.txt"',
        ]);
    }

    public function invoice(Order $order): Response
    {
        Gate::authorize('view', $order);

        $dto = $this->orderService->getOrder($order->id);
        $store = Store::find($order->store_id);

        $content = $this->a4Renderer->render($dto, [
            'store_name' => $store?->name,
            'store_address' => $store ? trim("{$store->address_line_1}, {$store->city} {$store->postal_code}") : '',
            'store_phone' => $store?->phone,
            'store_email' => $store?->email,
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="invoice-'.$order->order_number.'.html"',
        ]);
    }
}
