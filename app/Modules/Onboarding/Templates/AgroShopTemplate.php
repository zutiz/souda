<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class AgroShopTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'agro_shop';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Seeds', 'children' => [
                ['name' => 'Vegetable Seeds'],
                ['name' => 'Fruit Seeds'],
                ['name' => 'Grain Seeds'],
                ['name' => 'Flower Seeds'],
            ]],
            ['name' => 'Fertilizers', 'children' => [
                ['name' => 'Organic Fertilizers'],
                ['name' => 'Chemical Fertilizers'],
                ['name' => 'Soil Conditioners'],
            ]],
            ['name' => 'Pesticides & Herbicides', 'children' => [
                ['name' => 'Insecticides'],
                ['name' => 'Herbicides'],
                ['name' => 'Fungicides'],
            ]],
            ['name' => 'Tools & Equipment', 'children' => [
                ['name' => 'Hand Tools'],
                ['name' => 'Irrigation'],
                ['name' => 'Sprayers'],
            ]],
            ['name' => 'Animal Feed', 'children' => [
                ['name' => 'Poultry Feed'],
                ['name' => 'Livestock Feed'],
                ['name' => 'Fish Feed'],
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
                    'options' => ['Piece', 'kg', 'g', 'L', 'mL', 'Pack', 'Bag', 'Sack'],
                ],
                [
                    'slug' => 'is_organic',
                    'label' => 'Organic',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 3,
                ],
                [
                    'slug' => 'crop_type',
                    'label' => 'Crop Type',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 4,
                    'options' => ['Vegetables', 'Fruits', 'Grains', 'Cash Crops', 'Fodder', 'All'],
                ],
                [
                    'slug' => 'season',
                    'label' => 'Growing Season',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 5,
                    'options' => ['All Season', 'Rainy', 'Dry', 'Winter', 'Summer'],
                ],
                [
                    'slug' => 'expiry_date',
                    'label' => 'Expiry Date',
                    'type' => 'date',
                    'required' => false,
                    'section' => 'inventory',
                    'order' => 6,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
                'inventory' => ['title' => 'Inventory Control', 'order' => 3],
            ],
            'search_columns' => ['name', 'sku', 'barcode'],
            'list_columns' => ['name', 'category', 'price', 'stock', 'unit_type'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'seasonal_items', 'width' => 'half', 'order' => 2],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 3],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 4],
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
