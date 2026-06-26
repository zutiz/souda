<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class BakeryPack implements IndustryPack
{
    public function slug(): string
    {
        return 'bakery';
    }

    public function name(): string
    {
        return 'Bakery';
    }

    public function description(): string
    {
        return 'Bakery and pastry shop with recipe costing, batch production, and freshness tracking';
    }

    public function modules(): array
    {
        return [
            'product' => ['required' => true],
            'inventory' => ['required' => true],
            'order' => ['required' => true],
            'pos' => ['required' => true],
            'crm' => ['required' => true],
            'billing' => ['required' => true],
            'team' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Cake', 'children' => [
                    ['label' => 'All Items', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Recipes', 'route' => 'bakery.recipes'],
                ]],
                ['label' => 'Production', 'route' => 'bakery.production', 'icon' => 'Factory', 'children' => [
                    ['label' => 'Batch Production', 'route' => 'bakery.production'],
                    ['label' => 'Production Schedule', 'route' => 'bakery.schedule'],
                ]],
                ['label' => 'POS', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Production Cost', 'route' => 'reports.production-cost'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'reports.*', 'settings.*', 'bakery.*'],
            'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.*', 'reports.view', 'bakery.production.*'],
            'baker' => ['bakery.production.*', 'products.view', 'inventory.view', 'bakery.recipes.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'category'],
            'quick_actions' => [
                ['label' => 'Custom Order', 'action' => 'custom_order'],
                ['label' => 'Check Freshness', 'action' => 'check_freshness'],
            ],
            'checkout_fields' => [
                ['slug' => 'custom_message', 'label' => 'Message on Cake', 'required' => false],
                ['slug' => 'pickup_time', 'label' => 'Pickup Time', 'required' => false, 'type' => 'datetime'],
            ],
            'has_custom_orders' => true,
            'receipt_fields' => ['items', 'custom_message', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'daily_sales' => ['component' => 'RevenueSummary', 'title' => 'Daily Sales', 'width' => 'half', 'permission' => 'reports.view'],
            'production_queue' => ['component' => 'ProductionQueue', 'title' => 'Today\'s Production', 'width' => 'half', 'permission' => 'bakery.production.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Daily sales by item', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'production-cost' => ['name' => 'Production Cost', 'description' => 'Cost analysis per batch', 'permission' => 'reports.view', 'filters' => ['date_range', 'product'], 'export_formats' => ['pdf', 'csv']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'freshness_hours' => 24, 'production_lead_time_hours' => 4];
    }

    public function featureFlags(): array
    {
        return ['batch_production', 'recipe_costing', 'custom_orders', 'freshness_tracking'];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
