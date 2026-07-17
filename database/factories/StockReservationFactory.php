<?php

namespace Database\Factories;

use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => $this->faker->numberBetween(1, 50),
            'reference' => 'REF-'.$this->faker->unique()->randomNumber(6),
            'reference_type' => 'order',
            'status' => 'active',
        ];
    }

    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'consumed',
            'consumed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }
}
