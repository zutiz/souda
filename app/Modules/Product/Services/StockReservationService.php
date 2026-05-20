<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Enums\StockReservationStatusEnum;
use App\Modules\Product\Events\StockReservationCreated;
use App\Modules\Product\Events\StockReservationExpired;
use App\Modules\Product\Exceptions\InsufficientStockException;
use App\Modules\Product\Models\StockReservation;
use App\Modules\Product\Models\WarehouseStock;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    public function __construct(
        protected Dispatcher $events,
        protected StockLockService $lockService,
    ) {}

    public function reserve(
        int $warehouseId,
        ?string $productId,
        ?string $variantId,
        int $quantity,
        string $referenceType,
        int $referenceId,
        ?CarbonImmutable $expiresAt = null,
    ): StockReservation {
        $expiresAt ??= CarbonImmutable::now()->addHours(24);

        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $quantity, $referenceType, $referenceId, $expiresAt) {
            $stock = $this->lockService->lockStockRecord($warehouseId, $productId, $variantId);

            $available = $stock->quantity - $stock->reserved_quantity - $this->getActiveReservationQuantity($warehouseId, $productId, $variantId);

            if ($available < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId ?? $variantId ?? 'unknown',
                    requested: $quantity,
                    available: $available,
                );
            }

            $reservation = StockReservation::query()->create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'expires_at' => $expiresAt,
                'status' => StockReservationStatusEnum::Active,
            ]);

            $this->events->dispatch(new StockReservationCreated(
                reservationId: $reservation->id,
                productId: $productId ?? $variantId ?? 'unknown',
                variantId: $variantId,
                quantity: $quantity,
                referenceType: $referenceType,
                referenceId: $referenceId,
            ));

            return $reservation;
        });
    }

    public function consume(StockReservation $reservation): void
    {
        $reservation->markAsConsumed();
    }

    public function cancel(StockReservation $reservation): void
    {
        $reservation->markAsCancelled();
    }

    public function expireOldReservations(): int
    {
        $count = 0;

        StockReservation::query()
            ->where('status', StockReservationStatusEnum::Active)
            ->where('expires_at', '<=', now())
            ->chunkById(100, function (Collection $reservations) use (&$count) {
                foreach ($reservations as $reservation) {
                    $reservation->markAsExpired();

                    $this->events->dispatch(new StockReservationExpired(
                        reservationId: $reservation->id,
                        productId: $reservation->product_id ?? $reservation->variant_id ?? 'unknown',
                        variantId: $reservation->variant_id,
                    ));

                    $count++;
                }
            });

        return $count;
    }

    public function getActiveReservations(?string $productId, ?string $variantId = null): Collection
    {
        $query = StockReservation::query()->active();

        if ($productId !== null) {
            $query->where('product_id', $productId);
        }

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        return $query->get();
    }

    public function getAvailableQuantity(int $warehouseId, ?string $productId, ?string $variantId = null): int
    {
        $stock = WarehouseStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($stock === null) {
            return 0;
        }

        $activeReservations = $this->getActiveReservationQuantity($warehouseId, $productId, $variantId);

        return $stock->quantity - $stock->reserved_quantity - $activeReservations;
    }

    public function cleanupExpiredReservations(): int
    {
        return $this->expireOldReservations();
    }

    protected function getActiveReservationQuantity(int $warehouseId, ?string $productId, ?string $variantId): int
    {
        return (int) StockReservation::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->where('status', StockReservationStatusEnum::Active)
            ->where('expires_at', '>', now())
            ->sum('quantity');
    }
}
