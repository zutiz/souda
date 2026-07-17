<?php

namespace Database\Factories;

use App\Modules\Inventory\Models\InventoryCount;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryCountFactory extends Factory
{
    protected $model = InventoryCount::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'reference' => 'CNT-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1, 999),
            'type' => 'full',
            'status' => 'draft',
            'notes' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(['status' => 'in_progress']);
    }

    public function verified(): static
    {
        return $this->state(['status' => 'verified', 'verified_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed', 'completed_at' => now()]);
    }
}
