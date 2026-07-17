<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class CafePack implements IndustryPack
{
    public function slug(): string
    {
        return 'cafe';
    }

    public function name(): string
    {
        return 'Cafe';
    }

    public function description(): string
    {
        return 'Coffee shop and cafe with beverage customization, recipe costing, and quick service';
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
                ['label' => 'Menu', 'route' => 'products.index', 'icon' => 'Coffee', 'children' => [
                    ['label' => 'Menu Items', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Customizations', 'route' => 'products.customizations'],
                ]],
                ['label' => 'Orders', 'route' => 'orders.index', 'icon' => 'ClipboardList'],
                ['label' => 'POS', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Beverage Costing', 'route' => 'reports.beverage-costing'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'reports.*', 'settings.*'],
            'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.*', 'reports.view'],
            'barista' => ['pos.create', 'orders.create', 'orders.view', 'customers.create'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'category'],
            'quick_actions' => [
                ['label' => 'Dine-in', 'action' => 'set_dine_in'],
                ['label' => 'Takeaway', 'action' => 'set_takeaway'],
            ],
            'checkout_fields' => [
                ['slug' => 'customer_name', 'label' => 'Name', 'required' => false],
                ['slug' => 'order_type', 'label' => 'Type', 'required' => true],
            ],
            'has_takeaway' => true,
            'receipt_fields' => ['items', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'daily_revenue' => ['component' => 'RevenueSummary', 'title' => 'Today\'s Revenue', 'width' => 'half', 'permission' => 'reports.view'],
            'popular_beverages' => ['component' => 'PopularItemsTable', 'title' => 'Popular Beverages', 'width' => 'full', 'permission' => 'reports.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by item and category', 'permission' => 'reports.view', 'filters' => ['date_range', 'category'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'beverage-costing' => ['name' => 'Beverage Costing', 'description' => 'Cost analysis per beverage', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'default_tip_percentage' => 0, 'service_charge_percentage' => 0];
    }

    public function featureFlags(): array
    {
        return [
            'beverage_customization',
            'loyalty_program',
            'recipe_management',
            'recipe_consumption',
            'waste_tracking',
            'expiry_tracking',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
