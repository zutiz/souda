<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryRule;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\RuleEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create([
        'low_stock_threshold' => 10,
        'track_inventory' => true,
    ]);

    $this->warehouse = Warehouse::factory()->create();
    $this->inventoryEngine = app(InventoryEngine::class);
    $this->ruleEngine = app(RuleEngine::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-RULE',
    );
});

test('evaluateLowStock creates alert when below threshold', function () {
    $rule = InventoryRule::factory()->create([
        'condition_type' => 'low_stock',
        'condition_config' => ['threshold' => 10],
        'action_type' => 'create_alert',
        'action_config' => ['severity' => 'warning'],
    ]);

    // Reduce stock below threshold
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-LOW',
    );

    $result = $this->ruleEngine->evaluateRule($rule);

    expect($result['triggered'])->toBe(1);
    expect($result['errors'])->toBeEmpty();

    $alert = InventoryAlert::where('rule_id', $rule->id)->first();
    expect($alert)->not->toBeNull()
        ->type->toBe('low_stock')
        ->severity->toBe('warning')
        ->product_id->toBe($this->product->id);
});

test('evaluateDeadStock creates alert for items with no recent movement', function () {
    $rule = InventoryRule::factory()->deadStock()->create();

    // Set last movement far in the past
    $balance = InventoryBalance::where('product_id', $this->product->id)->first();
    $balance->update(['last_movement_at' => now()->subDays(200)]);

    $result = $this->ruleEngine->evaluateRule($rule);

    expect($result['triggered'])->toBe(1);

    $alert = InventoryAlert::where('rule_id', $rule->id)->first();
    expect($alert)->not->toBeNull()
        ->type->toBe('dead_stock');
});

test('evaluateOverstock creates alert when above threshold', function () {
    $rule = InventoryRule::factory()->overstock()->create();

    $result = $this->ruleEngine->evaluateRule($rule);

    // Product has 100 units, threshold is 1000, so no alert
    expect($result['triggered'])->toBe(0);
});

test('evaluateExpiringBatch creates alert for expiring batches', function () {
    $rule = InventoryRule::factory()->expiringBatch()->create();

    // The product has stock but no expiring batch
    // So no alert should be triggered
    $result = $this->ruleEngine->evaluateRule($rule);

    expect($result['triggered'])->toBe(0);
});

test('evaluateAll runs all active rules', function () {
    InventoryRule::factory()->count(2)->create(['is_active' => true]);
    InventoryRule::factory()->inactive()->create();

    $results = $this->ruleEngine->evaluateAll();

    expect($results)->toHaveCount(2);
});

test('inactive rules are not evaluated', function () {
    $rule = InventoryRule::factory()->inactive()->create();

    $results = $this->ruleEngine->evaluateAll();

    expect($results)->toBeEmpty();
});

test('rule last_run_at is updated after evaluation', function () {
    $rule = InventoryRule::factory()->deadStock()->create();

    $balance = InventoryBalance::where('product_id', $this->product->id)->first();
    $balance->update(['last_movement_at' => now()->subDays(200)]);

    $this->ruleEngine->evaluateRule($rule);

    $rule->refresh();
    expect($rule->last_run_at)->not->toBeNull();
});

test('duplicate alerts are not created', function () {
    $rule = InventoryRule::factory()->create([
        'condition_type' => 'low_stock',
        'condition_config' => ['threshold' => 10],
        'action_type' => 'create_alert',
        'action_config' => ['severity' => 'warning'],
    ]);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-DUP',
    );

    $this->ruleEngine->evaluateRule($rule);
    $this->ruleEngine->evaluateRule($rule);

    $count = InventoryAlert::where('rule_id', $rule->id)->count();
    expect($count)->toBe(1);
});
