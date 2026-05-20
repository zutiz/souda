<?php

declare(strict_types=1);

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\DTOs\EventEnvelope;

interface DomainEvent
{
    public function toEnvelope(): EventEnvelope;

    public function getEventName(): string;

    public function getCorrelationId(): string;

    public function getTenantId(): ?string;
}
