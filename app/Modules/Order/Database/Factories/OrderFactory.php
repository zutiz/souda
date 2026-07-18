<?php

declare(strict_types=1);

namespace App\Modules\Order\Database\Factories;

use App\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(1000, 50000);
        $tax = (int) ($subtotal * 0.1);
        $grandTotal = $subtotal + $tax;

        return [
            'tenant_id' => null,
            'store_id' => (string) Str::ulid(),
            'order_number' => 'ORD-'.now()->format('ymd').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_email' => $this->faker->email(),
            'status' => 'pending',
            'order_type' => 'in_store',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'pending',
            'currency' => 'BDT',
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => $grandTotal,
            'source' => 'pos',
            'placed_at' => now(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'delivery',
            'shipping_name' => $this->faker->name(),
            'shipping_phone' => $this->faker->phoneNumber(),
            'shipping_address_line_1' => $this->faker->streetAddress(),
            'shipping_city' => $this->faker->city(),
            'shipping_postal_code' => $this->faker->postcode(),
        ]);
    }
}
