<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\BusinessType\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'slug' => 'grocery',
                'name' => 'Grocery',
                'description' => 'Grocery store and supermarket operations',
                'icon' => 'ShoppingCart',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'pharmacy',
                'name' => 'Pharmacy',
                'description' => 'Pharmacy and drug store with prescription management',
                'icon' => 'Pill',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'restaurant',
                'name' => 'Restaurant',
                'description' => 'Full-service restaurant and dining',
                'icon' => 'UtensilsCrossed',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'kitchen'],
                ],
            ],
            [
                'slug' => 'cafe',
                'name' => 'Cafe',
                'description' => 'Coffee shop and cafe operations',
                'icon' => 'Coffee',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm'],
                ],
            ],
            [
                'slug' => 'bakery',
                'name' => 'Bakery',
                'description' => 'Bakery and pastry shop',
                'icon' => 'Cake',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm'],
                ],
            ],
            [
                'slug' => 'salon',
                'name' => 'Salon',
                'description' => 'Hair and beauty salon',
                'icon' => 'Scissors',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'appointment'],
                ],
            ],
            [
                'slug' => 'spa',
                'name' => 'Spa',
                'description' => 'Wellness spa and therapy center',
                'icon' => 'Sparkles',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'appointment'],
                ],
            ],
            [
                'slug' => 'electronics',
                'name' => 'Electronics',
                'description' => 'Electronics and gadget store',
                'icon' => 'Monitor',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'fashion',
                'name' => 'Fashion',
                'description' => 'Fashion, clothing and apparel store',
                'icon' => 'Shirt',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm'],
                ],
            ],
            [
                'slug' => 'cosmetics',
                'name' => 'Cosmetics',
                'description' => 'Cosmetics and beauty products store',
                'icon' => 'Palette',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm'],
                ],
            ],
            [
                'slug' => 'hardware',
                'name' => 'Hardware',
                'description' => 'Hardware and home improvement store',
                'icon' => 'Hammer',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'wholesale',
                'name' => 'Wholesale',
                'description' => 'Wholesale and bulk distribution business',
                'icon' => 'Warehouse',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'distribution',
                'name' => 'Distribution',
                'description' => 'Product distribution and logistics',
                'icon' => 'Truck',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'agro_shop',
                'name' => 'Agro Shop',
                'description' => 'Agricultural supplies and equipment store',
                'icon' => 'Sprout',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm', 'supplier'],
                ],
            ],
            [
                'slug' => 'bookstore',
                'name' => 'Bookstore',
                'description' => 'Bookstore and stationery shop',
                'icon' => 'BookOpen',
                'is_active' => true,
                'config_template' => [
                    'default_modules' => ['product', 'inventory', 'pos', 'crm'],
                ],
            ],
        ];

        foreach ($types as $type) {
            BusinessType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                $type,
            );
        }
    }
}
