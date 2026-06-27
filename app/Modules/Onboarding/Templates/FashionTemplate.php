<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class FashionTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'fashion';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => "Men's Clothing", 'children' => [
                ['name' => 'Tops & Shirts'],
                ['name' => 'Pants & Jeans'],
                ['name' => 'Outerwear'],
                ['name' => 'Suits & Blazers'],
            ]],
            ['name' => "Women's Clothing", 'children' => [
                ['name' => 'Dresses'],
                ['name' => 'Tops & Blouses'],
                ['name' => 'Pants & Skirts'],
                ['name' => 'Outerwear'],
            ]],
            ['name' => 'Footwear', 'children' => [
                ['name' => 'Sneakers'],
                ['name' => 'Formal Shoes'],
                ['name' => 'Sandals'],
                ['name' => 'Boots'],
            ]],
            ['name' => 'Accessories', 'children' => [
                ['name' => 'Bags'],
                ['name' => 'Belts'],
                ['name' => 'Watches'],
                ['name' => 'Jewelry'],
            ]],
            ['name' => "Children's Clothing"],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'sizes',
                    'label' => 'Available Sizes',
                    'type' => 'multiselect',
                    'required' => true,
                    'section' => 'details',
                    'order' => 1,
                    'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL'],
                ],
                [
                    'slug' => 'colors',
                    'label' => 'Available Colors',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                    'options' => ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Pink', 'Gray', 'Navy', 'Brown'],
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
                    'slug' => 'brand',
                    'label' => 'Brand',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 4,
                ],
                [
                    'slug' => 'season',
                    'label' => 'Season',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'classification',
                    'order' => 5,
                    'options' => ['All Season', 'Spring/Summer', 'Fall/Winter'],
                ],
                [
                    'slug' => 'gender',
                    'label' => 'Gender',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'classification',
                    'order' => 6,
                    'options' => ['Men', 'Women', 'Unisex', 'Kids'],
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
            ],
            'search_columns' => ['name', 'sku', 'barcode', 'brand', 'material'],
            'list_columns' => ['name', 'category', 'brand', 'price', 'stock'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'seasonal_trending', 'width' => 'half', 'order' => 2],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 3],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 4],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_variants' => true,
            'variant_attributes' => ['size', 'color'],
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
