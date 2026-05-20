<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

readonly class OrderAddressDTO
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public ?string $state,
        public string $postalCode,
        public string $country,
        public ?string $email,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'],
            addressLine1: $data['address_line_1'],
            addressLine2: $data['address_line_2'] ?? null,
            city: $data['city'],
            state: $data['state'] ?? null,
            postalCode: $data['postal_code'],
            country: $data['country'],
            email: $data['email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
