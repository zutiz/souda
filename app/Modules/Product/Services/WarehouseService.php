<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\WarehouseDTO;
use App\Modules\Product\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

class WarehouseService
{
    public function createWarehouse(WarehouseDTO $dto): Warehouse
    {
        if ($dto->isDefault) {
            Warehouse::query()->where('is_default', true)->update(['is_default' => false]);
        }

        return Warehouse::query()->create([
            'name' => $dto->name,
            'code' => $dto->code,
            'address_line_1' => $dto->address['line1'] ?? null,
            'address_line_2' => $dto->address['line2'] ?? null,
            'city' => $dto->address['city'] ?? null,
            'state' => $dto->address['state'] ?? null,
            'postal_code' => $dto->address['postal_code'] ?? null,
            'country' => $dto->address['country'] ?? null,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'is_active' => $dto->isActive,
            'is_default' => $dto->isDefault,
        ]);
    }

    public function updateWarehouse(Warehouse $warehouse, WarehouseDTO $dto): Warehouse
    {
        if ($dto->isDefault && ! $warehouse->is_default) {
            Warehouse::query()->where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse->update([
            'name' => $dto->name,
            'code' => $dto->code,
            'address_line_1' => $dto->address['line1'] ?? null,
            'address_line_2' => $dto->address['line2'] ?? null,
            'city' => $dto->address['city'] ?? null,
            'state' => $dto->address['state'] ?? null,
            'postal_code' => $dto->address['postal_code'] ?? null,
            'country' => $dto->address['country'] ?? null,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'is_active' => $dto->isActive,
            'is_default' => $dto->isDefault,
        ]);

        return $warehouse;
    }

    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        $warehouse->delete();

        return true;
    }

    public function listActiveWarehouses(): Collection
    {
        return Warehouse::query()->active()->orderBy('name')->get();
    }

    public function setDefaultWarehouse(Warehouse $warehouse): Warehouse
    {
        Warehouse::query()->where('is_default', true)->update(['is_default' => false]);

        $warehouse->update(['is_default' => true]);

        return $warehouse;
    }
}
