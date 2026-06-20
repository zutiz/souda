<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Tenancy\Contracts\TenantModeStrategy;
use App\Tenancy\Modes\DedicatedMode;
use App\Tenancy\Modes\SharedMode;
use Illuminate\Support\Facades\App;

class TenantManager
{
    protected ?Tenant $currentTenant = null;

    protected ?TenantModeStrategy $currentStrategy = null;

    protected bool $initialized = false;

    public function initialize(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
        $this->currentStrategy = $this->resolveStrategy($tenant);
        $this->currentStrategy->initialize($tenant);
        $this->initialized = true;
    }

    public function end(): void
    {
        if ($this->currentStrategy) {
            $this->currentStrategy->end();
        }

        $this->currentTenant = null;
        $this->currentStrategy = null;
        $this->initialized = false;
    }

    public function current(): ?Tenant
    {
        if ($this->initialized && $this->currentTenant) {
            return $this->currentTenant;
        }

        return null;
    }

    public function id(): ?string
    {
        return $this->current()?->id;
    }

    public function isShared(): bool
    {
        return $this->currentStrategy?->isShared() ?? false;
    }

    public function isDedicated(): bool
    {
        return $this->currentStrategy?->isDedicated() ?? false;
    }

    public function strategy(): ?TenantModeStrategy
    {
        return $this->currentStrategy;
    }

    public function databaseConnection(): string
    {
        return $this->currentStrategy?->databaseConnection() ?? config('database.default');
    }

    public function initialized(): bool
    {
        return $this->initialized;
    }

    public function resolveStrategy(?Tenant $tenant = null): TenantModeStrategy
    {
        $tenant = $tenant ?? $this->currentTenant;

        if ($tenant && $tenant->isDedicated()) {
            return App::make(DedicatedMode::class);
        }

        return App::make(SharedMode::class);
    }

    public function guessModeFromPlan(string $planSlug): string
    {
        $map = config('tenancy.plan_mode_map');

        return $map[$planSlug] ?? 'shared';
    }
}
