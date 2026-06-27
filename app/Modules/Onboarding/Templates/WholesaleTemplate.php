<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class WholesaleTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'wholesale';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Packaged Goods', 'children' => [
                ['name' => 'Food & Beverages'],
                ['name' => 'Household'],
                ['name' => 'Personal Care'],
            ]],
            ['name' => 'Bulk Items', 'children' => [
                ['name' => 'Grains & Cereals'],
                ['name' => 'Oils & Fats'],
                ['name' => 'Sweeteners'],
            ]],
            ['name' => 'Wholesale Only', 'children' => [
                ['name' => 'Raw Materials'],
                ['name' => 'Industrial Supplies'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'min_order_qty',
                    'label' => 'Minimum Order Qty',
                    'type' => 'number',
                    'required' => true,
                    'section' => 'ordering',
                    'order' => 1,
                ],
                [
                    'slug' => 'bulk_price',
                    'label' => 'Bulk Unit Price',
                    'type' => 'money',
                    'required' => false,
                    'section' => 'pricing',
                    'order' => 2,
                ],
                [
                    'slug' => 'case_quantity',
                    'label' => 'Units Per Case',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'ordering',
                    'order' => 3,
                ],
                [
                    'slug' => 'weight_kg',
                    'label' => 'Weight (kg)',
                    'type' => 'decimal',
                    'required' => false,
                    'section' => 'details',
                    'order' => 4,
                ],
                [
                    'slug' => 'unit_type',
                    'label' => 'Unit Type',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'details',
                    'order' => 5,
                    'options' => ['Piece', 'kg', 'L', 'Box', 'Case', 'Pallet', 'Ton'],
                ],
                [
                    'slug' => 'tier_pricing',
                    'label' => 'Tiered Pricing Available',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'pricing',
                    'order' => 6,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'ordering' => ['title' => 'Ordering', 'order' => 2],
                'pricing' => ['title' => 'Pricing', 'order' => 3],
            ],
            'search_columns' => ['name', 'sku', 'barcode'],
            'list_columns' => ['name', 'category', 'price', 'stock', 'min_order_qty'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'top_customers', 'width' => 'half', 'order' => 2],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 3],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 4],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'list',
            'bulk_pricing' => true,
            'wholesale_tiers' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking', 'bank_transfer'],
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
