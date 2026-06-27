<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class ElectronicsTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'electronics';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Phones & Tablets', 'children' => [
                ['name' => 'Smartphones'],
                ['name' => 'Feature Phones'],
                ['name' => 'Tablets'],
                ['name' => 'Accessories'],
            ]],
            ['name' => 'Computers', 'children' => [
                ['name' => 'Laptops'],
                ['name' => 'Desktops'],
                ['name' => 'Monitors'],
                ['name' => 'Peripherals'],
            ]],
            ['name' => 'Audio', 'children' => [
                ['name' => 'Headphones'],
                ['name' => 'Speakers'],
                ['name' => 'Home Theater'],
            ]],
            ['name' => 'Home Appliances', 'children' => [
                ['name' => 'Kitchen Appliances'],
                ['name' => 'Cleaning Appliances'],
            ]],
            ['name' => 'Accessories'],
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
                    'slug' => 'model',
                    'label' => 'Model Number',
                    'type' => 'string',
                    'required' => true,
                    'section' => 'details',
                    'order' => 2,
                ],
                [
                    'slug' => 'warranty_months',
                    'label' => 'Warranty (months)',
                    'type' => 'number',
                    'required' => true,
                    'section' => 'warranty',
                    'order' => 3,
                ],
                [
                    'slug' => 'color',
                    'label' => 'Color',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 4,
                ],
                [
                    'slug' => 'voltage',
                    'label' => 'Voltage',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'specifications',
                    'order' => 5,
                    'options' => ['110V', '220V', 'Dual Voltage', 'Battery'],
                ],
                [
                    'slug' => 'serial_number',
                    'label' => 'Serial Number',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'inventory',
                    'order' => 6,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'specifications' => ['title' => 'Specifications', 'order' => 2],
                'warranty' => ['title' => 'Warranty', 'order' => 3],
                'inventory' => ['title' => 'Inventory', 'order' => 4],
            ],
            'search_columns' => ['name', 'sku', 'barcode', 'brand', 'model'],
            'list_columns' => ['name', 'brand', 'model', 'price', 'stock'],
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
            'serial_number_tracking' => true,
            'warranty_registration' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking', 'installment'],
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
