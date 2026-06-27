<?php

declare(strict_types=1);

namespace App\Modules\Store\Database\Factories;

use App\Modules\Store\Enums\StoreStatusEnum;
use App\Modules\Store\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z0-9]{6}')),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address_line_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'timezone' => 'UTC',
            'currency' => 'BDT',
            'locale' => 'en',
            'status' => StoreStatusEnum::Active,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function provisioning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreStatusEnum::Provisioning,
        ]);
    }
}
