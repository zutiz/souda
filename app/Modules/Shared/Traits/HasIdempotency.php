<?php

declare(strict_types=1);

namespace App\Modules\Shared\Traits;

use App\Modules\Shared\Contracts\DomainEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasIdempotency
{
    protected function idempotencyKey(DomainEvent $event): string
    {
        $listenerName = class_basename(static::class);

        $eventId = $event->toEnvelope()->eventId;

        return "idempotent:{$listenerName}:{$eventId}";
    }

    protected function alreadyProcessed(DomainEvent $event): bool
    {
        return Cache::has($this->idempotencyKey($event));
    }

    protected function markProcessed(DomainEvent $event, int $ttl = 86400): void
    {
        Cache::put($this->idempotencyKey($event), true, $ttl);
    }

    protected function releaseIdempotency(DomainEvent $event): void
    {
        Cache::forget($this->idempotencyKey($event));
    }

    protected function logFailure(DomainEvent $event, \Throwable $e, string $context = ''): void
    {
        Log::error(class_basename(static::class).' permanently failed', [
            'event' => $event->getEventName(),
            'event_id' => $event->toEnvelope()->eventId,
            'correlation_id' => $event->getCorrelationId(),
            'error' => $e->getMessage(),
            'context' => $context,
        ]);
    }
}
