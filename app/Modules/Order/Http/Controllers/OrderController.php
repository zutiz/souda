<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderAddressDTO;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Http\Requests\StoreOrderRequest;
use App\Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use App\Modules\Order\Services\OrderTimelineService;
use App\Modules\Store\Services\StoreContextManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController
{
    use AuthorizesRequests;

    public function __construct(
        protected OrderService $orderService,
        protected OrderTimelineService $timelineService,
        protected StoreContextManager $storeContext,
    ) {}

    public function index(Request $request): Response
    {
        $storeId = $this->storeContext->id();

        $orders = $this->orderService->listOrders($storeId, $request->only([
            'status', 'order_type', 'customer_id', 'date_from', 'date_to', 'search',
        ]));

        return Inertia::render('Order/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'order_type', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Order/Create');
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $storeId = $this->storeContext->id();

        $shippingAddress = new OrderAddressDTO(
            name: $request->input('shipping_name', ''),
            phone: $request->input('shipping_phone', ''),
            addressLine1: $request->input('shipping_address_line_1', ''),
            addressLine2: $request->input('shipping_address_line_2'),
            city: $request->input('shipping_city', ''),
            state: $request->input('shipping_state'),
            postalCode: $request->input('shipping_postal_code', ''),
            country: $request->input('shipping_country', ''),
        );

        $lineItems = array_map(fn (array $item) => new LineItemDTO(
            productId: $item['product_id'],
            variantId: $item['variant_id'] ?? null,
            name: $item['name'],
            sku: $item['sku'] ?? '',
            quantity: (int) $item['quantity'],
            unitPrice: (int) $item['unit_price'],
            totalPrice: (int) $item['total_price'],
            taxAmount: isset($item['tax_amount']) ? (int) $item['tax_amount'] : null,
            discountAmount: isset($item['discount_amount']) ? (int) $item['discount_amount'] : null,
            warehouseId: $item['warehouse_id'] ?? null,
            metadata: null,
        ), $request->input('line_items', []));

        $dto = new OrderDTO(
            orderId: '',
            orderNumber: '',
            tenantId: '',
            storeId: $storeId,
            status: 'pending',
            subtotal: (int) $request->input('subtotal'),
            grandTotal: (int) $request->input('grand_total'),
            currency: $request->input('currency', 'BDT'),
            shippingAddress: $shippingAddress,
            lineItems: $lineItems,
            placedAt: new CarbonImmutable,
            customerId: $request->input('customer_id'),
            couponCode: $request->input('coupon_code'),
            notes: $request->input('notes'),
            paymentMethod: $request->input('payment_method'),
            orderType: $request->input('order_type', 'in_store'),
            paymentStatus: $request->input('payment_status', 'pending'),
            taxTotal: (int) ($request->input('tax_total', 0)),
            discountTotal: (int) ($request->input('discount_total', 0)),
            shippingTotal: (int) ($request->input('shipping_total', 0)),
            customerName: $request->input('customer_name'),
            customerPhone: $request->input('customer_phone'),
            customerEmail: $request->input('customer_email'),
            source: $request->input('source', 'pos'),
        );

        $this->orderService->createOrder($dto);

        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully.');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $dto = $this->orderService->getOrder($order->id);

        return Inertia::render('Order/Show', [
            'order' => $dto->toArray(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $this->orderService->updateStatus(
            orderId: $order->id,
            newStatus: $request->input('status'),
            reason: $request->input('reason'),
            changedBy: $request->user()?->id,
        );

        return redirect()->back()
            ->with('success', 'Order status updated.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $request->validate(['reason' => 'required|string|max:500']);

        $this->orderService->cancelOrder(
            orderId: $order->id,
            reason: $request->input('reason'),
            cancelledBy: $request->user()?->id,
        );

        return redirect()->back()
            ->with('success', 'Order cancelled.');
    }

    public function timeline(Order $order): Response
    {
        $this->authorize('view', $order);

        $dto = $this->orderService->getOrder($order->id);
        $events = $this->timelineService->getTimeline($order->id);

        return Inertia::render('Order/Timeline', [
            'order' => $dto->toArray(),
            'events' => $events,
        ]);
    }
}
