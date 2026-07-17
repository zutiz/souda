<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class TransferCancelled
{
    public function __construct(
        public int $transferId,
        public ?string $reason = null,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
