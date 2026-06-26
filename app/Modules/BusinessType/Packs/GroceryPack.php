<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class GroceryPack implements IndustryPack
{
    public function slug(): string
    {
        return 'grocery';
    }

    public function name(): string
    {
        return 'Grocery';
    }

    public function description(): string
    {
        return 'Grocery store and supermarket with perishable tracking, unit conversions, and supplier management';
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
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'ShoppingCart', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Units & Conversions', 'route' => 'grocery.units'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Perishable Tracking', 'route' => 'grocery.perishable'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Low Stock', 'route' => 'inventory.low-stock'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart', 'children' => [
                    ['label' => 'POS', 'route' => 'pos.index'],
                    ['label' => 'Orders', 'route' => 'orders.index'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Wastage Report', 'route' => 'reports.wastage'],
                    ['label' => 'Supplier Performance', 'route' => 'reports.supplier-performance'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => [
                'products.*', 'inventory.*', 'pos.*', 'orders.*',
                'customers.*', 'suppliers.*', 'reports.*', 'settings.*',
            ],
            'manager' => [
                'products.*', 'inventory.view', 'inventory.stock.adjust',
                'pos.*', 'orders.*', 'customers.*', 'reports.view',
            ],
            'cashier' => [
                'pos.*', 'orders.create', 'orders.view', 'customers.create',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode'],
            'quick_actions' => [
                ['label' => 'Weight Item', 'action' => 'weigh_item'],
                ['label' => 'Price Check', 'action' => 'price_check'],
            ],
            'checkout_fields' => [
                ['slug' => 'weight_kg', 'label' => 'Weight (kg)', 'required' => false, 'type' => 'decimal'],
            ],
            'has_weight_scale' => true,
            'supports_fractional_quantity' => true,
            'receipt_fields' => ['items', 'weight', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'daily_revenue' => ['component' => 'RevenueSummary', 'title' => 'Today\'s Revenue', 'width' => 'half', 'permission' => 'reports.view'],
            'expiring_items' => ['component' => 'ExpiringItemsWidget', 'title' => 'Expiring Soon', 'width' => 'half', 'permission' => 'inventory.view'],
            'top_selling' => ['component' => 'TopSellingTable', 'title' => 'Top Selling Items', 'width' => 'full', 'permission' => 'reports.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Daily sales breakdown', 'permission' => 'reports.view', 'filters' => ['date_range', 'category'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'wastage' => ['name' => 'Wastage Report', 'description' => 'Damaged and expired product loss', 'permission' => 'reports.view', 'filters' => ['date_range', 'category'], 'export_formats' => ['pdf', 'csv']],
            'supplier-performance' => ['name' => 'Supplier Performance', 'description' => 'Supplier delivery and quality metrics', 'permission' => 'reports.view', 'filters' => ['date_range', 'supplier'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'weight_unit' => 'kg',
            'enable_perishable_tracking' => true,
            'default_markup_percentage' => 20,
        ];
    }

    public function featureFlags(): array
    {
        return ['perishable_tracking', 'unit_conversion', 'supplier_portal', 'bulk_discounts'];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
