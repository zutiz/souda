<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Inventory\Models\InventoryRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryAlertFactory extends Factory
{
    protected $model = InventoryAlert::class;

    public function definition(): array
    {
        return [
            'rule_id' => InventoryRule::factory(),
            'type' => 'low_stock',
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->sentence(),
            'severity' => 'warning',
            'product_id' => null,
            'warehouse_id' => null,
        ];
    }

    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'dismissed_at' => now(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => now(),
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'critical',
        ]);
    }
}
