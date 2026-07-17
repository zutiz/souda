<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class AgroShopPack implements IndustryPack
{
    public function slug(): string
    {
        return 'agro_shop';
    }

    public function name(): string
    {
        return 'Agro Shop';
    }

    public function description(): string
    {
        return 'Agricultural supplies store with seed/fertilizer/pesticide tracking, batch/lot tracking, and seasonal inventory';
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
            'supplier' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Sprout', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'suppliers.*', 'reports.*', 'settings.*'],
            'manager' => ['products.*', 'inventory.view', 'inventory.stock.adjust', 'pos.*', 'orders.*', 'customers.*', 'reports.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'category'],
            'checkout_fields' => [
                ['slug' => 'quantity_kg', 'label' => 'Weight (kg)', 'required' => false, 'type' => 'decimal'],
                ['slug' => 'customer_type', 'label' => 'Farmer/Retailer', 'required' => false],
            ],
            'supports_fractional_quantity' => true,
            'receipt_fields' => ['items', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by product category', 'permission' => 'reports.view', 'filters' => ['date_range', 'category'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'weight_unit' => 'kg', 'enable_batch_tracking' => true];
    }

    public function featureFlags(): array
    {
        return [
            'batch_tracking',
            'seasonal_inventory',
            'unit_conversion',
            'expiry_tracking',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
