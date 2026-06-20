<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\BusinessType\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'slug' => 'product',
                'name' => 'Product Management',
                'description' => 'Products, categories, brands, variants, and attributes',
                'version' => '1.0.0',
                'is_core' => true,
            ],
            [
                'slug' => 'inventory',
                'name' => 'Inventory Management',
                'description' => 'Warehouses, stock movements, reservations, and transfers',
                'version' => '1.0.0',
                'dependencies' => ['product'],
                'is_core' => true,
            ],
            [
                'slug' => 'order',
                'name' => 'Order Management',
                'description' => 'Order lifecycle, fulfillment, and returns',
                'version' => '1.0.0',
                'dependencies' => ['product', 'inventory'],
                'is_core' => true,
            ],
            [
                'slug' => 'pos',
                'name' => 'Point of Sale',
                'description' => 'POS sessions, payments, and receipts',
                'version' => '1.0.0',
                'dependencies' => ['product', 'order'],
            ],
            [
                'slug' => 'crm',
                'name' => 'Customer Relationship Management',
                'description' => 'Customer profiles, segmentation, and communication',
                'version' => '1.0.0',
            ],
            [
                'slug' => 'billing',
                'name' => 'Billing & Subscriptions',
                'description' => 'Subscription plans, invoices, and payments',
                'version' => '1.0.0',
                'is_core' => true,
            ],
            [
                'slug' => 'team',
                'name' => 'Team Management',
                'description' => 'Users, roles, permissions, and seats',
                'version' => '1.0.0',
                'is_core' => true,
            ],
            [
                'slug' => 'supplier',
                'name' => 'Supplier Management',
                'description' => 'Vendor management, purchase orders, and procurement',
                'version' => '1.0.0',
                'dependencies' => ['product', 'inventory'],
            ],
            [
                'slug' => 'kitchen',
                'name' => 'Kitchen Display',
                'description' => 'Order production, prep tracking, and kitchen workflows',
                'version' => '1.0.0',
                'dependencies' => ['product', 'order'],
            ],
            [
                'slug' => 'appointment',
                'name' => 'Appointment Booking',
                'description' => 'Service booking, scheduling, and calendar management',
                'version' => '1.0.0',
            ],
            [
                'slug' => 'reporting',
                'name' => 'Reporting & Analytics',
                'description' => 'Custom reports, dashboards, and data exports',
                'version' => '1.0.0',
                'is_core' => true,
            ],
        ];

        foreach ($modules as $module) {
            Module::query()->updateOrCreate(
                ['slug' => $module['slug']],
                $module,
            );
        }
    }
}
