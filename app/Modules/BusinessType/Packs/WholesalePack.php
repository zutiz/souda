<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class WholesalePack implements IndustryPack
{
    public function slug(): string
    {
        return 'wholesale';
    }

    public function name(): string
    {
        return 'Wholesale';
    }

    public function description(): string
    {
        return 'Wholesale and bulk distribution with tiered pricing, minimum order quantities, and B2B customer management';
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
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Warehouse', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Tiered Pricing', 'route' => 'wholesale.pricing-tiers'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Warehouses', 'route' => 'warehouses.index'],
                ]],
                ['label' => 'Orders', 'route' => 'orders.index', 'icon' => 'ClipboardList', 'children' => [
                    ['label' => 'All Orders', 'route' => 'orders.index'],
                    ['label' => 'Bulk Orders', 'route' => 'orders.bulk'],
                    ['label' => 'Quotations', 'route' => 'orders.quotations'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Building2', 'children' => [
                    ['label' => 'B2B Customers', 'route' => 'customers.index'],
                    ['label' => 'Price Lists', 'route' => 'wholesale.price-lists'],
                ]],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Bulk Order Report', 'route' => 'reports.bulk-orders'],
                    ['label' => 'Customer Analytics', 'route' => 'reports.customer-analytics'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'suppliers.*', 'reports.*', 'settings.*', 'wholesale.*'],
            'manager' => ['products.*', 'inventory.view', 'inventory.stock.adjust', 'pos.*', 'orders.*', 'customers.*', 'reports.view', 'wholesale.pricing.*'],
            'sales_rep' => ['orders.create', 'orders.view', 'customers.*', 'products.view', 'wholesale.price-lists.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode'],
            'checkout_fields' => [
                ['slug' => 'customer_type', 'label' => 'Customer Type', 'required' => true],
                ['slug' => 'po_number', 'label' => 'PO Number', 'required' => false],
            ],
            'supports_tiered_pricing' => true,
            'supports_bulk_discounts' => true,
            'show_unit_price' => true,
            'receipt_fields' => ['items', 'unit_price', 'quantity', 'discount', 'subtotal', 'total'],
            'tender_types' => ['cash', 'bank_transfer', 'check', 'credit'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'revenue_summary' => ['component' => 'RevenueSummary', 'title' => 'Revenue', 'width' => 'half', 'permission' => 'reports.view'],
            'pending_orders' => ['component' => 'PendingOrdersList', 'title' => 'Pending Orders', 'width' => 'half', 'permission' => 'orders.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by customer tier and product', 'permission' => 'reports.view', 'filters' => ['date_range', 'customer_tier'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'bulk-orders' => ['name' => 'Bulk Order Report', 'description' => 'Analysis of large volume orders', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'customer-analytics' => ['name' => 'Customer Analytics', 'description' => 'B2B customer purchasing patterns', 'permission' => 'reports.view', 'filters' => ['date_range', 'customer'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'enable_tiered_pricing' => true, 'default_min_order_qty' => 10];
    }

    public function featureFlags(): array
    {
        return [
            'tiered_pricing',
            'bulk_discounts',
            'b2b_portal',
            'credit_accounts',
            'quotation_management',
            'batch_tracking',
            'expiry_tracking',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'overstock_detection',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
