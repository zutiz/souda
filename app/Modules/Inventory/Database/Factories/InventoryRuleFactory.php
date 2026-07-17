<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\InventoryRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryRuleFactory extends Factory
{
    protected $model = InventoryRule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'condition_type' => 'low_stock',
            'condition_config' => ['threshold' => 10],
            'action_type' => 'create_alert',
            'action_config' => ['severity' => 'warning'],
            'is_active' => true,
            'schedule' => 'every_fifteen_minutes',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function deadStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Dead Stock Detection',
            'condition_type' => 'dead_stock',
            'condition_config' => ['days' => 90],
            'action_type' => 'create_alert',
            'action_config' => ['severity' => 'warning'],
        ]);
    }

    public function overstock(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overstock Alert',
            'condition_type' => 'overstock',
            'condition_config' => ['threshold' => 1000],
            'action_type' => 'create_alert',
            'action_config' => ['severity' => 'info'],
        ]);
    }

    public function expiringBatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Expiry Alert',
            'condition_type' => 'expiring_batch',
            'condition_config' => ['days' => 30],
            'action_type' => 'create_alert',
            'action_config' => ['severity' => 'critical'],
        ]);
    }
}
