<?php

declare(strict_types=1);

namespace App\Modules\Store\Services;

use App\Modules\Store\DTOs\StoreDTO;
use App\Modules\Store\Events\StoreCreated;
use App\Modules\Store\Events\StoreDeleted;
use App\Modules\Store\Events\StoreStatusChanged;
use App\Modules\Store\Events\StoreUpdated;
use App\Modules\Store\Models\Store;
use Illuminate\Database\Eloquent\Collection;

class StoreService
{
    public function createStore(StoreDTO $dto): Store
    {
        if ($dto->isDefault) {
            Store::query()->where('is_default', true)->update(['is_default' => false]);
        }

        $store = Store::query()->create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'code' => $dto->code,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'address_line_1' => $dto->addressLine1,
            'address_line_2' => $dto->addressLine2,
            'city' => $dto->city,
            'state' => $dto->state,
            'postal_code' => $dto->postalCode,
            'country' => $dto->country,
            'timezone' => $dto->timezone,
            'currency' => $dto->currency,
            'locale' => $dto->locale,
            'status' => $dto->status,
            'is_default' => $dto->isDefault,
            'business_hours' => $dto->businessHours,
            'config' => $dto->config,
            'pos_settings' => $dto->posSettings,
            'sort_order' => $dto->sortOrder,
        ]);

        StoreCreated::dispatch($store);

        return $store;
    }

    public function updateStore(Store $store, StoreDTO $dto): Store
    {
        if ($dto->isDefault && ! $store->is_default) {
            Store::query()->where('is_default', true)->update(['is_default' => false]);
        }

        $previousStatus = $store->status;

        $store->update(array_filter([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'code' => $dto->code,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'address_line_1' => $dto->addressLine1,
            'address_line_2' => $dto->addressLine2,
            'city' => $dto->city,
            'state' => $dto->state,
            'postal_code' => $dto->postalCode,
            'country' => $dto->country,
            'timezone' => $dto->timezone,
            'currency' => $dto->currency,
            'locale' => $dto->locale,
            'status' => $dto->status,
            'business_hours' => $dto->businessHours,
            'config' => $dto->config,
            'pos_settings' => $dto->posSettings,
            'sort_order' => $dto->sortOrder,
        ], fn ($value) => $value !== null));

        StoreUpdated::dispatch($store);

        if ($previousStatus !== $store->status) {
            StoreStatusChanged::dispatch($store, $previousStatus, $store->status);
        }

        return $store->fresh();
    }

    public function deleteStore(Store $store): void
    {
        $store->delete();

        StoreDeleted::dispatch($store);
    }

    public function setDefaultStore(Store $store): Store
    {
        Store::query()->where('is_default', true)->update(['is_default' => false]);

        $store->update(['is_default' => true]);

        StoreUpdated::dispatch($store);

        return $store->fresh();
    }

    public function listActiveStores(): Collection
    {
        return Store::query()->active()->ordered()->get();
    }

    public function listAllStores(): Collection
    {
        return Store::query()->ordered()->get();
    }
}
