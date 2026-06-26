<?php

namespace App\Tenancy\Contracts;

use App\Models\Tenant;

interface TenantModeStrategy
{
    public function initialize(Tenant $tenant): void;

    public function end(): void;

    public function isShared(): bool;

    public function isDedicated(): bool;

    public function databaseConnection(): string;

    public function cachePrefix(): string;

    public function storagePrefix(): string;

    public function queuePrefix(): string;
}
