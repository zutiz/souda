<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTOs\AllocationResult;

interface StockAllocator
{
    public function allocate(array $lineItems): AllocationResult;
}
