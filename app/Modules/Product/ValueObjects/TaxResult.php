<?php

declare(strict_types=1);

namespace App\Modules\Product\ValueObjects;

readonly class TaxResult
{
    public function __construct(
        public int $netAmount,
        public int $taxAmount,
        public int $grossAmount,
        public array $appliedRates,
    ) {}

    public function toArray(): array
    {
        return [
            'net_amount' => $this->netAmount,
            'tax_amount' => $this->taxAmount,
            'gross_amount' => $this->grossAmount,
            'applied_rates' => $this->appliedRates,
        ];
    }
}
