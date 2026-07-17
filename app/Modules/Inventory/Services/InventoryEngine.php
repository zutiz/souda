<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Contracts\InventoryEngineInterface;
use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Events\InventoryAdjusted;
use App\Modules\Inventory\Events\InventoryBalanceUpdated;
use App\Modules\Inventory\Events\InventoryDeducted;
use App\Modules\Inventory\Events\InventoryRestored;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Events\StockDepleted;
use App\Modules\Inventory\Events\StockMovementCreated;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Costing\CostingEngine;
use App\Modules\Product\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class InventoryEngine implements InventoryEngineInterface
{
    public function __construct(
        private StockMovementEngine $movementEngine,
        private InventoryBalanceService $balanceService,
        private CostingEngine $costingEngine,
    ) {}

    public function recordMovement(
        string $productId,
        ?string $variantId,
        int $warehouseId,
        int $quantity,
        string $movementType,
        string $reference,
        ?int $unitCost = null,
        ?int $batchId = null,
        ?array $serialNumbers = null,
        ?string $description = null,
    ): InventoryLedger {
        $type = MovementTypeEnum::tryFrom($movementType);

        if ($type === null) {
            throw new \InvalidArgumentException("Invalid movement type: {$movementType}");
        }

        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $quantity, $type,
            $reference, $unitCost, $batchId, $serialNumbers, $description
        ) {
            $ledger = $this->movementEngine->record(
                productId: $productId,
                variantId: $variantId,
                warehouseId: $warehouseId,
                quantity: $quantity,
                type: $type,
                reference: $reference,
                unitCost: $unitCost,
                batchId: $batchId,
                serialNumbers: $serialNumbers,
                description: $description,
            );

            $this->costingEngine->processMovement($ledger);

            $previousBalance = $this->balanceService->getByProductAndWarehouse(
                productId: $productId,
                warehouseId: $warehouseId,
                variantId: $variantId,
            );

            $previousQuantity = $previousBalance?->quantity ?? 0;

            $balance = $this->balanceService->recalculate(
                productId: $productId,
                variantId: $variantId,
                warehouseId: $warehouseId,
            );

            StockMovementCreated::dispatch($ledger);
            InventoryBalanceUpdated::dispatch($balance, $previousQuantity);

            if ($balance->quantity <= 0 && $previousQuantity > 0) {
                StockDepleted::dispatch($productId, (string) $warehouseId, $variantId);
            }

            $product = Product::find($productId);
            if ($product && $balance->quantity <= $product->low_stock_threshold) {
                LowStockAlert::dispatch(
                    productId: $productId,
                    warehouseId: (string) $warehouseId,
                    currentQuantity: $balance->quantity,
                    threshold: (int) $product->low_stock_threshold,
                );
            }

            $movement = new InventoryMovementDTO(
                productId: $productId,
                variantId: $variantId,
                warehouseId: (string) $warehouseId,
                quantityChange: $quantity,
                quantityAfter: $balance->quantity,
                type: $type->value,
                referenceType: $type->value,
                referenceId: $reference,
                reason: $description,
                metadata: null,
                occurredAt: new CarbonImmutable,
            );

            if ($quantity < 0) {
                (new InventoryDeducted(
                    movement: $movement,
                    orderId: $reference,
                ))->dispatch();
            } elseif ($quantity > 0) {
                (new InventoryRestored(
                    movement: $movement,
                    orderId: $reference,
                ))->dispatch();
            } else {
                (new InventoryAdjusted(
                    movement: $movement,
                    reason: $description ?? 'zero-quantity movement',
                ))->dispatch();
            }

            return $ledger;
        });
    }

    public function getBalance(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): int {
        $balance = $this->balanceService->getByProductAndWarehouse(
            productId: $productId,
            warehouseId: $warehouseId,
            variantId: $variantId,
        );

        return $balance?->quantity ?? 0;
    }
}
