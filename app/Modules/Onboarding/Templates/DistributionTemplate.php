<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class DistributionTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'distribution';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Logistics Services', 'children' => [
                ['name' => 'Freight'],
                ['name' => 'Courier'],
                ['name' => 'Last Mile Delivery'],
            ]],
            ['name' => 'Warehousing'],
            ['name' => 'Fleet Management'],
            ['name' => 'Transport', 'children' => [
                ['name' => 'Road Transport'],
                ['name' => 'Air Freight'],
                ['name' => 'Sea Freight'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'weight_kg',
                    'label' => 'Weight (kg)',
                    'type' => 'decimal',
                    'required' => true,
                    'section' => 'cargo',
                    'order' => 1,
                ],
                [
                    'slug' => 'volume_cbm',
                    'label' => 'Volume (CBM)',
                    'type' => 'decimal',
                    'required' => false,
                    'section' => 'cargo',
                    'order' => 2,
                ],
                [
                    'slug' => 'is_hazmat',
                    'label' => 'Hazardous Material',
                    'type' => 'boolean',
                    'required' => true,
                    'section' => 'classification',
                    'order' => 3,
                ],
                [
                    'slug' => 'storage_requirements',
                    'label' => 'Storage Requirements',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'storage',
                    'order' => 4,
                    'options' => ['Ambient', 'Refrigerated', 'Frozen', 'Climate Controlled', 'Secure'],
                ],
                [
                    'slug' => 'handling_instructions',
                    'label' => 'Handling Instructions',
                    'type' => 'text',
                    'required' => false,
                    'section' => 'cargo',
                    'order' => 5,
                ],
                [
                    'slug' => 'origin',
                    'label' => 'Origin',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'routing',
                    'order' => 6,
                ],
                [
                    'slug' => 'destination',
                    'label' => 'Destination',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'routing',
                    'order' => 7,
                ],
            ],
            'sections' => [
                'cargo' => ['title' => 'Cargo Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
                'storage' => ['title' => 'Storage', 'order' => 3],
                'routing' => ['title' => 'Routing', 'order' => 4],
            ],
            'search_columns' => ['name', 'sku', 'origin', 'destination'],
            'list_columns' => ['name', 'category', 'weight_kg', 'volume_cbm'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_shipments', 'width' => 'half', 'order' => 1],
            ['widget' => 'fleet_status', 'width' => 'half', 'order' => 2],
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 3],
            ['widget' => 'pending_deliveries', 'width' => 'full', 'order' => 4],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'list',
            'tender_types' => ['cash', 'card', 'bank_transfer', 'credit'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'dispatcher', 'driver', 'cashier'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
            'new_delivery_alerts' => true,
            'delivery_status_updates' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }
}
