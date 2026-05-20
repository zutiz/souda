<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Exceptions\InsufficientStockException;

readonly class AllocationResult
{
    public function __construct(
        public bool $success,
        public array $allocations,
        public ?array $failedItems = null,
        public ?InsufficientStockException $error = null,
    ) {}

    public static function success(array $allocations): self
    {
        return new self(success: true, allocations: $allocations);
    }

    public static function failed(array $allocations, array $failedItems, InsufficientStockException $error): self
    {
        return new self(
            success: false,
            allocations: $allocations,
            failedItems: $failedItems,
            error: $error,
        );
    }
}
