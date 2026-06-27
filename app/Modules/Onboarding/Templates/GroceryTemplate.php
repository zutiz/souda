<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class GroceryTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'grocery';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Fruits & Vegetables', 'children' => [
                ['name' => 'Fruits'],
                ['name' => 'Vegetables'],
                ['name' => 'Herbs'],
            ]],
            ['name' => 'Dairy & Eggs', 'children' => [
                ['name' => 'Milk'],
                ['name' => 'Cheese'],
                ['name' => 'Yogurt'],
                ['name' => 'Eggs'],
            ]],
            ['name' => 'Beverages', 'children' => [
                ['name' => 'Soft Drinks'],
                ['name' => 'Juices'],
                ['name' => 'Water'],
            ]],
            ['name' => 'Snacks'],
            ['name' => 'Household'],
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
                    'required' => false,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'unit_type',
                    'label' => 'Unit Type',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'details',
                    'order' => 2,
                    'options' => ['Piece', 'kg', 'g', 'L', 'mL', 'Pack'],
                ],
                [
                    'slug' => 'is_perishable',
                    'label' => 'Perishable',
                    'type' => 'boolean',
                    'required' => true,
                    'section' => 'storage',
                    'order' => 3,
                ],
                [
                    'slug' => 'is_organic',
                    'label' => 'Organic',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 4,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'storage' => ['title' => 'Storage', 'order' => 2],
                'classification' => ['title' => 'Classification', 'order' => 3],
            ],
            'search_columns' => ['name', 'sku', 'barcode'],
            'list_columns' => ['name', 'category', 'price', 'stock', 'unit_type'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'expiring_items', 'width' => 'half', 'order' => 2],
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
            'low_stock_alerts' => true,
            'expiry_alerts' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }

    public function defaultStores(): array
    {
        return [
            [
                'name' => 'Main Store',
                'slug' => 'main',
                'code' => 'STORE-001',
                'currency' => 'XOF',
                'timezone' => 'Africa/Porto-Novo',
                'is_default' => true,
                'status' => 'active',
            ],
        ];
    }
}
