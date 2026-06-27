<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Contracts;

interface TenantTemplate
{
    public function businessType(): string;

    public function defaultCategories(): array;

    public function productSchema(): array;

    public function dashboardLayout(): array;

    public function posDefaults(): array;

    public function defaultTeam(): array;

    public function notificationDefaults(): array;

    public function initialData(): array;

    public function defaultStores(): array;
}
