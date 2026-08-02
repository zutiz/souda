<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class HardwarePack implements IndustryPack
{
    public function slug(): string
    {
        return 'hardware';
    }

    public function name(): string
    {
        return 'Hardware';
    }

    public function description(): string
    {
        return 'Hardware store with unit conversions (pieces, meters, kg), measurement-based inventory, and supplier management';
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
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Hammer', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Units of Measure', 'route' => 'hardware.units'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                ]],
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
            'salesperson' => ['pos.*', 'orders.create', 'customers.create', 'products.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode', 'category'],
            'quick_actions' => [['label' => 'Sell by Meter', 'action' => 'sell_by_meter']],
            'checkout_fields' => [
                ['slug' => 'quantity_unit', 'label' => 'Unit', 'required' => true],
                ['slug' => 'measurement_value', 'label' => 'Measurement', 'required' => false, 'type' => 'decimal'],
            ],
            'supports_fractional_quantity' => true,
            'supports_unit_conversion' => true,
            'receipt_fields' => ['items', 'unit', 'measurement', 'subtotal', 'total'],
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
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by category and brand', 'permission' => 'reports.view', 'filters' => ['date_range', 'category'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'default_unit' => 'piece', 'enable_fractional_quantity' => true];
    }

    public function featureFlags(): array
    {
        return [
            'fractional_quantity',
            'unit_conversion',
            'measurement_sales',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
            'overstock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.60 0.15 25)',      // Industrial orange
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.50 0.02 0)',        // Steel gray
            'accent_foreground' => 'oklch(0.985 0 0)',
            'sidebar' => 'oklch(0.25 0 0)',
            'sidebar_foreground' => 'oklch(0.985 0 0)',
            'sidebar_accent' => 'oklch(0.40 0.04 25)',
            'radius' => '0.5rem',                    // Standard, industrial
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
