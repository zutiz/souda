<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Http\Requests\CreateShipmentRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\Shipment;
use App\Modules\Order\Services\FulfillmentService;
use App\Modules\Order\Services\ShipmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController
{
    use AuthorizesRequests;

    public function __construct(
        protected ShipmentService $shipmentService,
        protected FulfillmentService $fulfillmentService,
    ) {}

    public function index(Order $order): Response
    {
        $shipments = $this->shipmentService->listShipments($order->id);

        return Inertia::render('Order/Shipments/Index', [
            'order' => $order->toArray(),
            'shipments' => $shipments,
        ]);
    }

    public function show(Order $order, Shipment $shipment): Response
    {
        $dto = $this->shipmentService->getShipment($shipment->id);

        return Inertia::render('Order/Shipments/Show', [
            'order' => $order->toArray(),
            'shipment' => $dto->toArray(),
        ]);
    }

    public function store(CreateShipmentRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $this->fulfillmentService->fulfillOrder(
            orderId: $order->id,
            courier: $request->input('courier'),
            items: $request->input('items', []),
            recipient: [
                'name' => $request->input('recipient_name'),
                'phone' => $request->input('recipient_phone'),
                'address' => $request->input('recipient_address'),
                'city' => $request->input('recipient_city'),
                'postal_code' => $request->input('recipient_postal_code'),
            ],
            declaredValue: (int) $request->input('declared_value'),
            codAmount: (int) $request->input('cod_amount', 0),
            totalWeightGrams: $request->input('total_weight_grams') ? (int) $request->input('total_weight_grams') : null,
            notes: $request->input('notes'),
        );

        return redirect()->route('orders.shipments.index', $order->id)
            ->with('success', 'Shipment created.');
    }

    public function track(Request $request, Order $order, Shipment $shipment): Response
    {
        $tracking = $this->shipmentService->trackShipment($shipment->id);

        return Inertia::render('Order/Shipments/Track', [
            'order' => $order->toArray(),
            'shipment' => $tracking,
        ]);
    }
}
