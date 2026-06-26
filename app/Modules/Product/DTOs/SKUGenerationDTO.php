<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

readonly class SKUGenerationDTO
{
    public function __construct(
        public string $productId,
        public string $productSku,
        public array $variants,
    ) {}
}
