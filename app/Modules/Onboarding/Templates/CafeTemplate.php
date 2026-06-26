<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class CafeTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'cafe';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Coffee', 'children' => [
                ['name' => 'Espresso Based'],
                ['name' => 'Brewed Coffee'],
                ['name' => 'Cold Brew'],
            ]],
            ['name' => 'Tea', 'children' => [
                ['name' => 'Hot Tea'],
                ['name' => 'Iced Tea'],
                ['name' => 'Herbal Tea'],
            ]],
            ['name' => 'Cold Drinks', 'children' => [
                ['name' => 'Smoothies'],
                ['name' => 'Juices'],
                ['name' => 'Frappes'],
            ]],
            ['name' => 'Pastries'],
            ['name' => 'Light Meals'],
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
                    'section' => 'preparation',
                    'order' => 1,
                ],
                [
                    'slug' => 'temperature',
                    'label' => 'Temperature',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                    'options' => ['Hot', 'Cold', 'Iced', 'Blended', 'Room Temperature'],
                ],
                [
                    'slug' => 'size_options',
                    'label' => 'Size Options',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'details',
                    'order' => 3,
                    'options' => ['Small', 'Medium', 'Large', 'Extra Large'],
                ],
                [
                    'slug' => 'caffeine_free',
                    'label' => 'Caffeine Free',
                    'type' => 'boolean',
                    'required' => false,
                    'section' => 'dietary',
                    'order' => 4,
                ],
                [
                    'slug' => 'dietary_tags',
                    'label' => 'Dietary Tags',
                    'type' => 'multiselect',
                    'required' => false,
                    'section' => 'dietary',
                    'order' => 5,
                    'options' => ['Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Sugar-Free'],
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Item Details', 'order' => 1],
                'preparation' => ['title' => 'Preparation', 'order' => 2],
                'dietary' => ['title' => 'Dietary Info', 'order' => 3],
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
            ['widget' => 'popular_items', 'width' => 'full', 'order' => 3],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_tables' => true,
            'has_takeaway' => true,
            'has_kitchen_display' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'barista', 'server', 'cashier'],
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
}
