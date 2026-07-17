<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SerialNumberFactory extends Factory
{
    protected $model = SerialNumber::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'serial_number' => $this->faker->unique()->regexify('[A-Z0-9]{15}'),
            'status' => 'available',
        ];
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
            'order_reference' => 'ORD-'.$this->faker->randomNumber(5),
            'sold_at' => now(),
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'returned',
        ]);
    }

    public function withWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expires_at' => now()->addYear(),
        ]);
    }
}
