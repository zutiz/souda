<?php

declare(strict_types=1);

namespace App\Modules\Store\Services;

use App\Modules\Store\Models\Store;

class StoreContextManager
{
    protected ?Store $currentStore = null;

    protected bool $initialized = false;

    public function initialize(Store $store): void
    {
        $this->currentStore = $store;
        $this->initialized = true;
    }

    public function end(): void
    {
        $this->currentStore = null;
        $this->initialized = false;
    }

    public function current(): ?Store
    {
        return $this->currentStore;
    }

    public function id(): ?string
    {
        return $this->currentStore?->id;
    }

    public function initialized(): bool
    {
        return $this->initialized;
    }

    public function __toString(): string
    {
        return $this->currentStore?->id ?? '';
    }
}
