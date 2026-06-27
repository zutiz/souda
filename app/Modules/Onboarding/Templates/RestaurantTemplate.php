<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class RestaurantTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'restaurant';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Appetizers'],
            ['name' => 'Main Course', 'children' => [
                ['name' => 'Chicken'],
                ['name' => 'Beef'],
                ['name' => 'Seafood'],
                ['name' => 'Vegetarian'],
            ]],
            ['name' => 'Beverages', 'children' => [
                ['name' => 'Soft Drinks'],
                ['name' => 'Juices'],
                ['name' => 'Hot Beverages'],
            ]],
            ['name' => 'Desserts'],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'preparation_time',
                    'label' => 'Prep Time (min)',
                    'type' => 'number',
                    'required' => true,
                    'section' => 'kitchen',
                    'order' => 1,
                ],
                [
                    'slug' => 'spice_level',
                    'label' => 'Spice Level',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                    'options' => ['Mild', 'Medium', 'Spicy', 'Very Spicy'],
                ],
                [
                    'slug' => 'is_vegetarian',
                    'label' => 'Vegetarian',
                    'type' => 'boolean',
                    'required' => true,
                    'section' => 'dietary',
                    'order' => 3,
                ],
                [
                    'slug' => 'is_vegan',
                    'label' => 'Vegan',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'dietary',
                    'order' => 4,
                ],
                [
                    'slug' => 'allergens',
                    'label' => 'Allergens',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'dietary',
                    'order' => 5,
                    'options' => ['Dairy', 'Eggs', 'Fish', 'Shellfish', 'Tree Nuts', 'Peanuts', 'Wheat', 'Soy'],
                ],
                [
                    'slug' => 'recipe_yield',
                    'label' => 'Recipe Yield',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'costing',
                    'order' => 6,
                ],
                [
                    'slug' => 'cost_per_serving',
                    'label' => 'Cost Per Serving',
                    'type' => 'money',
                    'required' => false,
                    'section' => 'costing',
                    'order' => 7,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Item Details', 'order' => 1],
                'dietary' => ['title' => 'Dietary Info', 'order' => 2],
                'kitchen' => ['title' => 'Kitchen', 'order' => 3],
                'costing' => ['title' => 'Costing', 'order' => 4],
            ],
            'search_columns' => ['name', 'sku', 'category'],
            'list_columns' => ['name', 'category', 'price', 'preparation_time'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'todays_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'active_orders', 'width' => 'half', 'order' => 2],
            ['widget' => 'food_cost_percentage', 'width' => 'half', 'order' => 3],
            ['widget' => 'kitchen_queue', 'width' => 'half', 'order' => 4],
            ['widget' => 'popular_items', 'width' => 'full', 'order' => 5],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_tables' => true,
            'has_takeaway' => true,
            'has_delivery' => true,
            'has_kitchen_display' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'chef', 'server', 'cashier'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
            'new_order_alerts' => true,
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
