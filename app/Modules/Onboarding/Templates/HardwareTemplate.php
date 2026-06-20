<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class HardwareTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'hardware';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Tools', 'children' => [
                ['name' => 'Hand Tools'],
                ['name' => 'Power Tools'],
                ['name' => 'Tool Accessories'],
            ]],
            ['name' => 'Building Materials', 'children' => [
                ['name' => 'Cement & Aggregates'],
                ['name' => 'Bricks & Blocks'],
                ['name' => 'Timber'],
                ['name' => 'Roofing'],
            ]],
            ['name' => 'Plumbing', 'children' => [
                ['name' => 'Pipes & Fittings'],
                ['name' => 'Faucets & Valves'],
                ['name' => 'Water Heaters'],
            ]],
            ['name' => 'Electrical', 'children' => [
                ['name' => 'Cables & Wires'],
                ['name' => 'Switches & Sockets'],
                ['name' => 'Lighting'],
                ['name' => 'Circuit Breakers'],
            ]],
            ['name' => 'Paint & Finishing', 'children' => [
                ['name' => 'Paints'],
                ['name' => 'Brushes & Rollers'],
                ['name' => 'Thinners & Sealants'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'brand',
                    'label' => 'Brand',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'weight_kg',
                    'label' => 'Weight (kg)',
                    'type' => 'decimal',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                ],
                [
                    'slug' => 'material',
                    'label' => 'Material',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 3,
                ],
                [
                    'slug' => 'warranty_months',
                    'label' => 'Warranty (months)',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'warranty',
                    'order' => 4,
                ],
                [
                    'slug' => 'unit_type',
                    'label' => 'Unit Type',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'details',
                    'order' => 5,
                    'options' => ['Piece', 'kg', 'm', 'L', 'Pack', 'Box', 'Bag'],
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'warranty' => ['title' => 'Warranty', 'order' => 2],
            ],
            'search_columns' => ['name', 'sku', 'barcode', 'brand'],
            'list_columns' => ['name', 'category', 'brand', 'price', 'stock', 'unit_type'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 2],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 3],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_weight_scale' => true,
            'supports_fractional_quantity' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'cashier'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
            'low_stock_alerts' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }
}
