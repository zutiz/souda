<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Events\StockReservationCancelled;
use App\Modules\Inventory\Events\StockReservationCreated;
use App\Modules\Inventory\Events\StockReservationExpired;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\ReservationNotFoundException;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\StockReservation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReservationEngine
{
    public function __construct(
        private InventoryBalanceService $balanceService,
    ) {}

    public function reserve(
        int $warehouseId,
        string $productId,
        ?string $variantId,
        int $quantity,
        string $reference,
        string $referenceType,
        ?CarbonImmutable $expiresAt = null,
    ): StockReservation {
        $expiresAt ??= CarbonImmutable::now()->addMinutes(
            (int) config('inventory.reservation_ttl_minutes', 30),
        );

        return DB::transaction(function () use (
            $warehouseId, $productId, $variantId, $quantity,
            $reference, $referenceType, $expiresAt,
        ) {
            $balance = InventoryBalance::where([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
            ])->lockForUpdate()->first();

            $onHand = $balance?->quantity ?? 0;
            $reservedQty = $balance?->reserved_quantity ?? 0;
            $activeReservations = (int) StockReservation::where([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'status' => 'active',
            ])->sum('quantity');

            $available = $onHand - $reservedQty - $activeReservations;

            if ($available < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId,
                    requested: $quantity,
                    available: max(0, $available),
                );
            }

            $reservation = StockReservation::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'reference' => $reference,
                'reference_type' => $referenceType,
                'expires_at' => $expiresAt,
                'status' => 'active',
            ]);

            event(new StockReservationCreated(
                reservationId: $reservation->id,
                productId: $productId,
                variantId: $variantId,
                warehouseId: $warehouseId,
                quantity: $quantity,
            ));

            return $reservation;
        });
    }

    public function consume(int $reservationId): StockReservation
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::lockForUpdate()->find($reservationId);

            if ($reservation === null) {
                throw new ReservationNotFoundException($reservationId);
            }

            $reservation->markAsConsumed();

            return $reservation;
        });
    }

    public function cancel(int $reservationId): StockReservation
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::lockForUpdate()->find($reservationId);

            if ($reservation === null) {
                throw new ReservationNotFoundException($reservationId);
            }

            $reservation->markAsCancelled();

            event(new StockReservationCancelled(
                reservationId: $reservation->id,
                productId: $reservation->product_id,
                variantId: $reservation->variant_id,
                warehouseId: $reservation->warehouse_id,
            ));

            return $reservation;
        });
    }

    public function expireOldReservations(): int
    {
        $count = 0;

        StockReservation::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function (Collection $reservations) use (&$count) {
                foreach ($reservations as $reservation) {
                    $reservation->markAsExpired();

                    event(new StockReservationExpired(
                        reservationId: $reservation->id,
                        productId: $reservation->product_id,
                        variantId: $reservation->variant_id,
                        warehouseId: $reservation->warehouse_id,
                    ));

                    $count++;
                }
            });

        return $count;
    }

    public function getActiveReservations(
        string $productId,
        ?int $warehouseId = null,
        ?string $variantId = null,
    ): Collection {
        $query = StockReservation::where('product_id', $productId)
            ->where('status', 'active');

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        return $query->get();
    }

    public function getAvailableQuantity(
        int $warehouseId,
        string $productId,
        ?string $variantId = null,
    ): int {
        $balance = $this->balanceService->getByProductAndWarehouse(
            productId: $productId,
            warehouseId: $warehouseId,
            variantId: $variantId,
        );

        if ($balance === null) {
            return 0;
        }

        $activeReservations = (int) StockReservation::where([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'status' => 'active',
        ])->sum('quantity');

        return $balance->quantity - $balance->reserved_quantity - $activeReservations;
    }
}
