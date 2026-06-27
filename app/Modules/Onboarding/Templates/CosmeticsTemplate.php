<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class CosmeticsTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'cosmetics';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Makeup', 'children' => [
                ['name' => 'Face'],
                ['name' => 'Eyes'],
                ['name' => 'Lips'],
                ['name' => 'Nails'],
            ]],
            ['name' => 'Skincare', 'children' => [
                ['name' => 'Cleansers'],
                ['name' => 'Moisturizers'],
                ['name' => 'Serums & Treatments'],
                ['name' => 'Sunscreen'],
            ]],
            ['name' => 'Hair Care', 'children' => [
                ['name' => 'Shampoo & Conditioner'],
                ['name' => 'Styling Products'],
                ['name' => 'Hair Treatments'],
            ]],
            ['name' => 'Fragrances', 'children' => [
                ['name' => 'Perfumes'],
                ['name' => 'Body Sprays'],
                ['name' => 'Deodorants'],
            ]],
            ['name' => 'Tools & Accessories'],
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
                    'required' => true,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'shade',
                    'label' => 'Shade / Color',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                ],
                [
                    'slug' => 'volume_ml',
                    'label' => 'Volume (mL)',
                    'type' => 'decimal',
                    'required' => false,
                    'section' => 'details',
                    'order' => 3,
                ],
                [
                    'slug' => 'skin_type',
                    'label' => 'Skin Type',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 4,
                    'options' => ['All', 'Normal', 'Dry', 'Oily', 'Combination', 'Sensitive'],
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
                    'slug' => 'expiry_months',
                    'label' => 'Shelf Life (months)',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'inventory',
                    'order' => 6,
                ],
                [
                    'slug' => 'is_organic',
                    'label' => 'Organic / Natural',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 7,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
                'inventory' => ['title' => 'Inventory', 'order' => 3],
            ],
            'search_columns' => ['name', 'sku', 'barcode', 'brand', 'shade'],
            'list_columns' => ['name', 'brand', 'category', 'price', 'stock'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 2],
            ['widget' => 'top_brands', 'width' => 'half', 'order' => 3],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 4],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_variants' => true,
            'variant_attributes' => ['shade', 'size'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'sales_rep', 'cashier'],
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
