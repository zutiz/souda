<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Contracts;

use App\Models\Tenant;

interface IndustryPack
{
    public function slug(): string;

    public function name(): string;

    public function description(): string;

    public function modules(): array;

    public function menus(): array;

    public function permissions(): array;

    public function posConfig(): array;

    public function dashboardWidgets(): array;

    public function reportDefinitions(): array;

    public function defaultSettings(): array;

    public function featureFlags(): array;

    public function branding(): array;

    public function onTenantAssigned(Tenant $tenant): void;

    public function onTenantRemoved(Tenant $tenant): void;
}
