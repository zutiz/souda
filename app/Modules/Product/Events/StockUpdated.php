<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use App\Modules\Product\DTOs\StockMovementDTO;
use Carbon\CarbonImmutable;

readonly class StockUpdated
{
    public function __construct(
        public StockMovementDTO $movement,
        public int $previousAvailable,
        public int $newAvailable,
        public ?array $snapshotBefore,
        public ?array $snapshotAfter,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
