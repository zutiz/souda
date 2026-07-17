<?php

namespace Database\Factories;

use App\Modules\Inventory\Models\PurchaseSuggestion;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseSuggestionFactory extends Factory
{
    protected $model = PurchaseSuggestion::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'variant_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'current_quantity' => fake()->numberBetween(0, 50),
            'reserved_quantity' => fake()->numberBetween(0, 10),
            'available_quantity' => fn (array $attrs) => $attrs['current_quantity'] - $attrs['reserved_quantity'],
            'reorder_level' => 10,
            'lead_time_days' => 7,
            'safety_stock' => 0,
            'sales_velocity' => fake()->randomFloat(2, 0.5, 20),
            'suggested_quantity' => fake()->numberBetween(10, 100),
            'status' => 'pending',
        ];
    }

    public function ordered(): static
    {
        return $this->state(['status' => 'ordered']);
    }

    public function dismissed(): static
    {
        return $this->state(['status' => 'dismissed']);
    }
}
