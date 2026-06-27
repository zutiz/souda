<?php

declare(strict_types=1);

namespace App\Modules\Store\DTOs;

use App\Modules\Store\Models\Store;

readonly class StoreDTO
{
    public function __construct(
        public ?string $id,
        public string $name,
        public ?string $slug,
        public ?string $code,
        public ?string $email,
        public ?string $phone,
        public ?string $addressLine1,
        public ?string $addressLine2,
        public ?string $city,
        public ?string $state,
        public ?string $postalCode,
        public ?string $country,
        public ?string $timezone,
        public ?string $currency,
        public ?string $locale,
        public ?string $status,
        public bool $isDefault,
        public ?array $businessHours,
        public ?array $config,
        public ?array $posSettings,
        public int $sortOrder,
    ) {}

    public static function fromModel(Store $store): self
    {
        return new self(
            id: $store->id,
            name: $store->name,
            slug: $store->slug,
            code: $store->code,
            email: $store->email,
            phone: $store->phone,
            addressLine1: $store->address_line_1,
            addressLine2: $store->address_line_2,
            city: $store->city,
            state: $store->state,
            postalCode: $store->postal_code,
            country: $store->country,
            timezone: $store->timezone,
            currency: $store->currency,
            locale: $store->locale,
            status: $store->status,
            isDefault: $store->is_default,
            businessHours: $store->business_hours,
            config: $store->config,
            posSettings: $store->pos_settings,
            sortOrder: $store->sort_order,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            slug: $data['slug'] ?? null,
            code: $data['code'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            addressLine1: $data['address_line_1'] ?? null,
            addressLine2: $data['address_line_2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            timezone: $data['timezone'] ?? null,
            currency: $data['currency'] ?? null,
            locale: $data['locale'] ?? null,
            status: $data['status'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
            businessHours: $data['business_hours'] ?? null,
            config: $data['config'] ?? null,
            posSettings: $data['pos_settings'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
