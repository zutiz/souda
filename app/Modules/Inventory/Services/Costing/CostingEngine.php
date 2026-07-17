<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Costing;

use App\Modules\Inventory\Enums\CostingMethodEnum;
use App\Modules\Inventory\Exceptions\CostingMethodNotSupportedException;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Costing\Contracts\CostingStrategyInterface;
use App\Modules\Inventory\Services\Costing\Strategies\FifoCosting;
use App\Modules\Inventory\Services\Costing\Strategies\LifoCosting;
use App\Modules\Inventory\Services\Costing\Strategies\WeightedAverageCosting;

class CostingEngine
{
    private CostingStrategyInterface $strategy;

    public function __construct(?string $method = null)
    {
        $method ??= config('inventory.default_costing_method', 'weighted_average');

        $this->strategy = match ($method) {
            CostingMethodEnum::WeightedAverage->value => app(WeightedAverageCosting::class),
            CostingMethodEnum::Fifo->value => app(FifoCosting::class),
            CostingMethodEnum::Lifo->value => app(LifoCosting::class),
            default => throw new CostingMethodNotSupportedException($method),
        };
    }

    public function processMovement(InventoryLedger $ledger): void
    {
        if ($ledger->quantity > 0) {
            $this->strategy->processInbound($ledger);
        } elseif ($ledger->quantity < 0) {
            $this->strategy->processOutbound($ledger);
        }
    }
}
