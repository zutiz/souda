<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\InventoryTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransferFactory extends Factory
{
    protected $model = InventoryTransfer::class;

    public function definition(): array
    {
        $prefix = 'TRF-'.now()->format('Ymd');

        return [
            'reference' => $prefix.'-'.$this->faker->unique()->randomNumber(4),
            'from_warehouse_id' => WarehouseFactory::new(),
            'to_warehouse_id' => WarehouseFactory::new(),
            'status' => 'draft',
            'description' => $this->faker->sentence(),
        ];
    }

    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_transit',
            'sent_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'sent_at' => now()->subHour(),
            'received_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
