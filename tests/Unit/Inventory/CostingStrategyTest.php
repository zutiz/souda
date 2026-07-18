<?php

declare(strict_types=1);

use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Costing\Strategies\FifoCosting;
use App\Modules\Inventory\Services\Costing\Strategies\WeightedAverageCosting;

function weightedAverageLedger(int $qty, ?int $unitCost = null, string $productId = 'prod-1', int $warehouseId = 1): InventoryLedger
{
    $ledger = Mockery::mock(InventoryLedger::class)->makePartial();
    $ledger->product_id = $productId;
    $ledger->variant_id = null;
    $ledger->warehouse_id = $warehouseId;
    $ledger->quantity = $qty;
    $ledger->unit_cost = $unitCost;

    return $ledger;
}

function setupInventoryBalanceMock(?int $qty = null, ?int $avgCost = null, ?int $totalValue = null): ?object
{
    if ($qty === null) {
        return null;
    }

    $balance = Mockery::mock('stdClass');
    $balance->product_id = 'prod-1';
    $balance->variant_id = null;
    $balance->warehouse_id = 1;
    $balance->quantity = $qty;
    $balance->average_unit_cost = $avgCost ?? 0;
    $balance->total_stock_value = $totalValue ?? 0;
    $balance->shouldReceive('updateQuietly')
        ->andReturnUsing(function (array $attrs) use ($balance) {
            foreach ($attrs as $key => $value) {
                $balance->$key = $value;
            }

            return true;
        });

    return $balance;
}

function setupInventoryBalanceQuery(?object $balance): void
{
    $alias = Mockery::mock('alias:App\Modules\Inventory\Models\InventoryBalance');

    $query = Mockery::mock('stdClass');
    $query->shouldReceive('first')
        ->andReturn($balance);

    $alias->shouldReceive('where')
        ->with(Mockery::type('array'))
        ->andReturn($query);
}

function setupCostLayerQuery(array $layers): void
{
    $query = Mockery::mock('stdClass');
    $query->shouldReceive('where')
        ->with('quantity_remaining', '>', 0)
        ->andReturnSelf();
    $query->shouldReceive('orderBy')
        ->with('id')
        ->andReturnSelf();
    $query->shouldReceive('get')
        ->andReturn(collect($layers));

    $alias = Mockery::mock('alias:App\Modules\Inventory\Models\CostLayer');
    $alias->shouldReceive('where')
        ->with('product_id', Mockery::type('string'))
        ->andReturnSelf();
    $alias->shouldReceive('where')
        ->with('warehouse_id', Mockery::type('int'))
        ->andReturnSelf();
    $alias->shouldReceive('where')
        ->with('variant_id', Mockery::type('null'))
        ->andReturn($query);
}

function fifoCostLayer(int $qty, int $cost, int $remaining, int $id = 1): object
{
    $layer = Mockery::mock('stdClass');
    $layer->id = $id;
    $layer->quantity_remaining = $remaining;
    $layer->unit_cost = $cost;
    $layer->shouldReceive('decrement')
        ->andReturnUsing(function (string $column, int $amount) use ($layer) {
            $layer->quantity_remaining -= $amount;

            return true;
        });

    return $layer;
}

describe('WeightedAverageCosting', function () {
    beforeEach(function () {
        $this->strategy = new WeightedAverageCosting;
    });

    test('inbound with null unit cost is skipped', function () {
        $ledger = weightedAverageLedger(50, null);

        $result = $this->strategy->processInbound($ledger);

        expect($result)->toBeNull();
    });

    test('inbound updates average cost when balance exists', function () {
        $ledger = weightedAverageLedger(50, 7000);
        $balance = setupInventoryBalanceMock(100, 5000);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processInbound($ledger);

        expect($balance->average_unit_cost)->toBe(5667);
    });

    test('inbound uses zero defaults when no prior balance', function () {
        $ledger = weightedAverageLedger(50, 7000);

        setupInventoryBalanceQuery(null);

        $this->strategy->processInbound($ledger);
    });

    test('inbound with zero total quantity keeps average at zero', function () {
        $ledger = weightedAverageLedger(0, 5000);
        $balance = setupInventoryBalanceMock(0, 0);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processInbound($ledger);

        expect($balance->average_unit_cost)->toBe(0);
    });

    test('outbound is skipped when no balance exists', function () {
        $ledger = weightedAverageLedger(-30, null);

        setupInventoryBalanceQuery(null);

        $result = $this->strategy->processOutbound($ledger);

        expect($result)->toBeNull();
    });

    test('outbound is skipped when average cost is zero', function () {
        $ledger = weightedAverageLedger(-30, null);

        $balance = setupInventoryBalanceMock(100, 0);
        setupInventoryBalanceQuery($balance);

        $result = $this->strategy->processOutbound($ledger);

        expect($result)->toBeNull();
    });

    test('outbound reduces total stock value', function () {
        $ledger = weightedAverageLedger(-30, null);
        $ledger->shouldReceive('updateQuietly')
            ->andReturnTrue();

        $balance = setupInventoryBalanceMock(100, 5000, 500000);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processOutbound($ledger);

        expect($balance->total_stock_value)->toBe(350000);
    });
});

describe('FifoCosting', function () {
    beforeEach(function () {
        $this->strategy = new FifoCosting;
    });

    test('inbound with null unit cost is skipped', function () {
        $ledger = weightedAverageLedger(50, null);

        $result = $this->strategy->processInbound($ledger);

        expect($result)->toBeNull();
    });

    test('outbound consumes layers in FIFO order', function () {
        $ledger = weightedAverageLedger(-60, null);
        $ledger->shouldReceive('updateQuietly')
            ->andReturnTrue();

        $layer1 = fifoCostLayer(100, 1000, 50, 1);
        $layer2 = fifoCostLayer(100, 2000, 100, 2);
        $layer3 = fifoCostLayer(100, 3000, 100, 3);

        setupCostLayerQuery([$layer1, $layer2, $layer3]);

        $balance = setupInventoryBalanceMock(200, 0, 500000);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processOutbound($ledger);

        expect($layer1->quantity_remaining)->toBe(0);
        expect($layer2->quantity_remaining)->toBe(90);
        expect($layer3->quantity_remaining)->toBe(100);
    });

    test('outbound with zero remaining quantity does nothing', function () {
        $ledger = weightedAverageLedger(-60, null);
        $ledger->shouldReceive('updateQuietly')
            ->andReturnTrue();

        setupCostLayerQuery([]);

        $balance = setupInventoryBalanceMock(200, 0, 500000);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processOutbound($ledger);
    });

    test('multiple outbound calls consume progressively', function () {
        $layer1 = fifoCostLayer(100, 1000, 100, 1);
        $layer2 = fifoCostLayer(100, 2000, 100, 2);

        setupCostLayerQuery([$layer1, $layer2]);

        $ledger1 = weightedAverageLedger(-60, null);
        $ledger1->shouldReceive('updateQuietly')->andReturnTrue();

        $balance = setupInventoryBalanceMock(200, 0, 300000);
        setupInventoryBalanceQuery($balance);

        $this->strategy->processOutbound($ledger1);
    });
});
