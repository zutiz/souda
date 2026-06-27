<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class BakeryTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'bakery';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Breads', 'children' => [
                ['name' => 'White Bread'],
                ['name' => 'Whole Wheat'],
                ['name' => 'Sourdough'],
                ['name' => 'Specialty Breads'],
            ]],
            ['name' => 'Pastries', 'children' => [
                ['name' => 'Croissants'],
                ['name' => 'Danishes'],
                ['name' => 'Muffins'],
            ]],
            ['name' => 'Cakes', 'children' => [
                ['name' => 'Layer Cakes'],
                ['name' => 'Cupcakes'],
                ['name' => 'Cheesecakes'],
            ]],
            ['name' => 'Cookies'],
            ['name' => 'Savory Items'],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'weight_g',
                    'label' => 'Weight (g)',
                    'type' => 'decimal',
                    'required' => false,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'batch_date',
                    'label' => 'Batch Date',
                    'type' => 'date',
                    'required' => true,
                    'section' => 'production',
                    'order' => 2,
                ],
                [
                    'slug' => 'expiry_date',
                    'label' => 'Expiry Date',
                    'type' => 'date',
                    'required' => true,
                    'section' => 'production',
                    'order' => 3,
                ],
                [
                    'slug' => 'is_artisan',
                    'label' => 'Artisan Made',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 4,
                ],
                [
                    'slug' => 'ingredients',
                    'label' => 'Key Ingredients',
                    'type' => 'text',
                    'required' => false,
                    'section' => 'details',
                    'order' => 5,
                ],
                [
                    'slug' => 'dietary_tags',
                    'label' => 'Dietary Tags',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'dietary',
                    'order' => 6,
                    'options' => ['Vegetarian', 'Vegan', 'Gluten-Free', 'Sugar-Free', 'Dairy-Free', 'Nut-Free'],
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'production' => ['title' => 'Production', 'order' => 2],
                'classification' => ['title' => 'Classification', 'order' => 3],
                'dietary' => ['title' => 'Dietary Info', 'order' => 4],
            ],
            'search_columns' => ['name', 'sku', 'category'],
            'list_columns' => ['name', 'category', 'price', 'weight_g', 'expiry_date'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_production', 'width' => 'half', 'order' => 1],
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 2],
            ['widget' => 'expiring_items', 'width' => 'half', 'order' => 3],
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
            'roles' => ['admin', 'manager', 'baker', 'cashier'],
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
