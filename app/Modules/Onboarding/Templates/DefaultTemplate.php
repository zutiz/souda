<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class DefaultTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'general';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'General'],
            ['name' => 'Services'],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [],
            'sections' => [
                'details' => ['title' => 'Details', 'order' => 1],
            ],
            'search_columns' => ['name', 'sku'],
            'list_columns' => ['name', 'price', 'stock'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'recent_orders', 'width' => 'half', 'order' => 2],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }
}
