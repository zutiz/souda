<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use App\Modules\Product\DTOs\VariantDTO;
use App\Modules\Product\Models\Variant;
use Carbon\CarbonImmutable;

readonly class VariantUpdated
{
    public function __construct(
        public VariantDTO $variant,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}

    public static function fromModel(Variant $variant): self
    {
        return new self(
            variant: VariantDTO::fromModel($variant),
        );
    }
}
