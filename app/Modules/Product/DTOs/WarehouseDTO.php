<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\Warehouse;

readonly class WarehouseDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $code,
        public ?array $address,
        public ?string $phone,
        public ?string $email,
        public bool $isActive,
        public bool $isDefault,
    ) {}

    public static function fromModel(Warehouse $warehouse): self
    {
        return new self(
            id: $warehouse->id,
            name: $warehouse->name,
            code: $warehouse->code,
            address: [
                'line1' => $warehouse->address_line_1,
                'line2' => $warehouse->address_line_2,
                'city' => $warehouse->city,
                'state' => $warehouse->state,
                'postal_code' => $warehouse->postal_code,
                'country' => $warehouse->country,
            ],
            phone: $warehouse->phone,
            email: $warehouse->email,
            isActive: $warehouse->is_active,
            isDefault: $warehouse->is_default,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            code: $data['code'],
            address: [
                'line1' => $data['address_line_1'] ?? null,
                'line2' => $data['address_line_2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? null,
            ],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            isDefault: (bool) ($data['is_default'] ?? false),
        );
    }
}
