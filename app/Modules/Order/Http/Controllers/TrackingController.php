<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Models\Shipment;
use App\Modules\Order\Services\CourierManager;
use App\Modules\Order\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController
{
    public function __construct(
        protected CourierManager $courierManager,
    ) {}

    public function show(string $trackingNumber): Response
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

        if (! $shipment) {
            abort(404, 'Shipment not found.');
        }

        $tracking = [];

        if ($shipment->courier && $shipment->tracking_number) {
            try {
                $tracking = $this->courierManager->trackShipment(
                    $shipment->courier,
                    $shipment->tracking_number,
                );
            } catch (\Throwable) {
                $tracking = [];
            }
        }

        return Inertia::render('Order/Tracking', [
            'shipment' => $shipment->toArray(),
            'tracking' => $tracking,
        ]);
    }

    public function webhook(Request $request, string $courier): JsonResponse
    {
        $validCouriers = ['pathao', 'steadfast', 'redx', 'sendo', 'paperfly'];

        if (! in_array($courier, $validCouriers, true)) {
            return response()->json(['error' => 'Invalid courier.'], 400);
        }

        try {
            $provider = $this->courierManager->driver($courier);

            $signature = $request->header('X-Webhook-Signature', '');
            $verified = $provider->validateWebhookSignature(
                $request->all(),
                $signature,
            );

            if (! $verified) {
                return response()->json(['error' => 'Invalid signature.'], 401);
            }

            $trackingNumber = $request->input('tracking_number');
            $status = $request->input('status');

            if ($trackingNumber && $status) {
                $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

                if ($shipment) {
                    app(ShipmentService::class)->updateStatus(
                        shipmentId: $shipment->id,
                        newStatus: $this->mapCourierStatus($status),
                        notes: $request->input('message'),
                    );
                }
            }

            return response()->json(['received' => true]);
        } catch (\Throwable $e) {
            logger()->error('Courier webhook failed', [
                'courier' => $courier,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed.'], 500);
        }
    }

    private function mapCourierStatus(string $courierStatus): string
    {
        return match (strtolower($courierStatus)) {
            'picked_up', 'pickup', 'collected' => 'picked_up',
            'in_transit', 'transit', 'shipped' => 'in_transit',
            'out_for_delivery', 'delivery_attempt', 'with_delivery_man' => 'out_for_delivery',
            'delivered', 'completed', 'success' => 'delivered',
            'failed', 'delivery_failed', 'cancelled_by_customer', 'cancel' => 'delivery_failed',
            'returned', 'return_to_sender', 'rts' => 'returned_to_sender',
            default => 'in_transit',
        };
    }
}
